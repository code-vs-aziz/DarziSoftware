<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'core/Api_Controller.php';

/**
 * Public shop API — catalog + COD checkout.
 * Routes: /api/shop/{method}
 */
class Shop extends Api_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->ensure_schema();
	}

	public function categories()
	{
		return $this->ok(array('categories' => $this->website_model->list_categories(TRUE)));
	}

	public function products()
	{
		$q = trim((string) $this->input->get('q', TRUE));
		$category_id = (int) $this->input->get('category_id');
		$featured = (int) $this->input->get('featured') === 1;
		$products = $this->website_model->list_products(array(
			'q' => $q,
			'category_id' => $category_id,
			'featured' => $featured,
			'limit' => min(48, max(1, (int) $this->input->get('limit') ?: 24)),
			'offset' => max(0, (int) $this->input->get('offset')),
		));
		foreach ($products as &$p)
		{
			$images = $this->website_model->get_product_images((int) $p['id']);
			$p['image'] = ! empty($images[0]['image_url']) ? $images[0]['image_url'] : '';
			$p['price'] = (float) $p['price'];
		}
		unset($p);
		return $this->ok(array('products' => $products));
	}

	public function product($slug = '')
	{
		$slug = trim((string) $slug);
		$product = $this->website_model->get_product_by_slug($slug);
		if ( ! $product)
		{
			return $this->fail('not_found', 404);
		}
		$product['price'] = (float) $product['price'];
		$product['images'] = $this->website_model->get_product_images((int) $product['id']);
		return $this->ok(array('product' => $product));
	}

	public function checkout()
	{
		if ($this->input->method(TRUE) !== 'POST')
		{
			return $this->fail('method_not_allowed', 405);
		}
		if ( ! $this->website_model->rate_limit_hit('checkout:'.$this->client_ip(), 3600, 20))
		{
			return $this->fail('rate_limited', 429);
		}

		$input = $this->json_input();
		$items_in = isset($input['items']) && is_array($input['items']) ? $input['items'] : array();
		if (count($items_in) < 1)
		{
			return $this->fail('empty_cart');
		}

		$customer = $this->CI_website_customer();
		$guest_name = isset($input['name']) ? trim((string) $input['name']) : '';
		$guest_phone = isset($input['phone']) ? trim((string) $input['phone']) : '';
		$address = isset($input['address']) ? trim((string) $input['address']) : '';
		$note = isset($input['note']) ? trim((string) $input['note']) : '';
		$payment_method = isset($input['payment_method']) ? (string) $input['payment_method'] : 'cod';
		if ( ! in_array($payment_method, array('cod', 'pay_in_shop'), TRUE))
		{
			$payment_method = 'cod';
		}

		if ( ! $customer && ($guest_name === '' || $guest_phone === ''))
		{
			return $this->fail('customer_required');
		}

		$line_rows = array();
		$subtotal = 0.0;
		foreach ($items_in as $item)
		{
			$product_id = isset($item['product_id']) ? (int) $item['product_id'] : 0;
			$qty = isset($item['qty']) ? (int) $item['qty'] : 0;
			if ($product_id <= 0 || $qty < 1 || $qty > 50)
			{
				return $this->fail('invalid_item');
			}
			$product = $this->db->where('id', $product_id)->where('is_active', 1)->get('web_products')->row_array();
			if ( ! $product)
			{
				return $this->fail('product_unavailable');
			}
			$unit = (float) $product['price'];
			$line_total = round($unit * $qty, 2);
			$subtotal += $line_total;
			$line_rows[] = array(
				'product_id' => $product_id,
				'product_name' => $product['name_en'],
				'sku' => $product['sku'],
				'unit_price' => $unit,
				'qty' => $qty,
				'line_total' => $line_total,
			);
		}

		$order_number = 'WB'.date('ymd').strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
		$order_id = $this->website_model->create_order(array(
			'order_number' => $order_number,
			'customer_code' => $customer ? (int) $customer['code'] : NULL,
			'guest_name' => $customer ? (string) $customer['name'] : $guest_name,
			'guest_phone' => $customer ? (string) $customer['Phone'] : $guest_phone,
			'guest_email' => isset($input['email']) ? trim((string) $input['email']) : '',
			'shipping_address' => $address,
			'status' => 'pending',
			'payment_method' => $payment_method,
			'payment_status' => 'unpaid',
			'subtotal' => $subtotal,
			'discount' => 0,
			'shipping_fee' => 0,
			'total' => $subtotal,
			'currency' => 'PKR',
			'customer_note' => substr($note, 0, 1000),
			'ip_address' => substr($this->client_ip(), 0, 45),
			'created_at' => date('Y-m-d H:i:s'),
		), $line_rows);

		if ($order_id <= 0)
		{
			return $this->fail('order_failed', 500);
		}

		return $this->ok(array(
			'order_id' => $order_id,
			'order_number' => $order_number,
			'total' => $subtotal,
			'status' => 'pending',
			'payment_method' => $payment_method,
		), 201);
	}

	public function order($order_number = '')
	{
		$order_number = trim((string) $order_number);
		if ($order_number === '')
		{
			return $this->fail('not_found', 404);
		}
		$customer = $this->CI_website_customer();
		$order = $this->website_model->get_order_by_number($order_number, $customer ? (int) $customer['code'] : 0);
		if ( ! $order)
		{
			// Guests can look up with phone last-4 in future; for now require match or auth
			if ( ! $customer)
			{
				$order = $this->website_model->get_order_by_number($order_number, 0);
				$phone = trim((string) $this->input->get('phone', TRUE));
				if ( ! $order || $phone === '' || substr(preg_replace('/\D+/', '', $order['guest_phone']), -4) !== substr(preg_replace('/\D+/', '', $phone), -4))
				{
					return $this->fail('not_found', 404);
				}
			}
			else
			{
				return $this->fail('not_found', 404);
			}
		}
		$items = $this->website_model->get_order_items((int) $order['id']);
		return $this->ok(array('order' => $order, 'items' => $items));
	}
}
