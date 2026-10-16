<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ERP admin: Manage Website (CMS, products, orders, measurement submissions).
 */
class Website extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Website_model', 'website_model');
		$this->load->library('Website_measurement_service', NULL, 'website_measurement');
	}

	public function index()
	{
		$data = array(
			'table_ready' => $this->website_model->has_tables(),
			'settings' => $this->website_model->get_settings(),
			'counts' => $this->website_model->has_tables() ? $this->website_model->dashboard_counts() : array(),
			'sections' => $this->website_model->has_tables() ? $this->website_model->get_home_sections(FALSE) : array(),
			'active_tab' => 'overview',
		);
		$this->load->view('website_manage', $data);
	}

	public function settings()
	{
		$data = array(
			'table_ready' => $this->website_model->has_tables(),
			'settings' => $this->website_model->get_settings(),
			'counts' => $this->website_model->has_tables() ? $this->website_model->dashboard_counts() : array(),
			'active_tab' => 'settings',
		);
		$this->load->view('website_manage', $data);
	}

	public function save_settings()
	{
		if ( ! $this->website_model->has_tables())
		{
			$this->session->set_flashdata('error', 'missing_table');
			redirect('Website');
			return;
		}
		$ok = $this->website_model->save_settings(array(
			'site_name' => $this->input->post('site_name', TRUE),
			'tagline_en' => $this->input->post('tagline_en', TRUE),
			'tagline_ur' => $this->input->post('tagline_ur', FALSE),
			'about_en' => $this->input->post('about_en', FALSE),
			'about_ur' => $this->input->post('about_ur', FALSE),
			'contact_phone' => $this->input->post('contact_phone', TRUE),
			'contact_whatsapp' => $this->input->post('contact_whatsapp', TRUE),
			'contact_email' => $this->input->post('contact_email', TRUE),
			'contact_address_en' => $this->input->post('contact_address_en', FALSE),
			'contact_address_ur' => $this->input->post('contact_address_ur', FALSE),
			'seo_title_default' => $this->input->post('seo_title_default', TRUE),
			'seo_description_default' => $this->input->post('seo_description_default', TRUE),
			'og_image_url' => $this->input->post('og_image_url', TRUE),
			'feature_shop' => $this->input->post('feature_shop') ? 1 : 0,
			'feature_measurements' => $this->input->post('feature_measurements') ? 1 : 0,
		));
		$this->session->set_flashdata($ok ? 'suss' : 'error', $ok ? 'yes' : 'save_failed');
		redirect('Website/settings');
	}

	public function submissions()
	{
		$status = trim((string) $this->input->get('status', TRUE));
		if ($status === '')
		{
			$status = 'pending';
		}
		$rows = array();
		if ($this->website_model->has_tables())
		{
			$rows = $this->website_model->list_measurement_submissions($status === 'all' ? '' : $status, 100, 0);
			foreach ($rows as &$row)
			{
				$cust = $this->website_model->get_customer_by_code((int) $row['customer_code']);
				$row['customer_name'] = $cust ? $cust['name'] : '';
				$row['man_napno'] = $cust ? $cust['man_napno'] : '';
			}
			unset($row);
		}
		$data = array(
			'table_ready' => $this->website_model->has_tables(),
			'settings' => $this->website_model->get_settings(),
			'counts' => $this->website_model->has_tables() ? $this->website_model->dashboard_counts() : array(),
			'submissions' => $rows,
			'filter_status' => $status,
			'active_tab' => 'submissions',
		);
		$this->load->view('website_manage', $data);
	}

	public function submission_view($id = 0)
	{
		$submission = $this->website_model->get_measurement_submission((int) $id);
		if ( ! $submission)
		{
			$this->session->set_flashdata('error', 'not_found');
			redirect('Website/submissions');
			return;
		}
		$customer = $this->website_model->get_customer_by_code((int) $submission['customer_code']);
		$payload = json_decode($submission['payload_json'], TRUE);
		$data = array(
			'table_ready' => TRUE,
			'settings' => $this->website_model->get_settings(),
			'counts' => $this->website_model->dashboard_counts(),
			'submission' => $submission,
			'customer' => $customer,
			'payload' => is_array($payload) ? $payload : array(),
			'active_tab' => 'submissions',
		);
		$this->load->view('website_submission_view', $data);
	}

	public function submission_approve($id = 0)
	{
		$note = (string) $this->input->post('review_note', TRUE);
		$user = (string) $this->session->userdata('user');
		$result = $this->website_measurement->approve((int) $id, $user, $note);
		$this->session->set_flashdata($result['ok'] ? 'suss' : 'error', $result['ok'] ? 'approved' : (isset($result['error']) ? $result['error'] : 'failed'));
		redirect('Website/submissions');
	}

	public function submission_reject($id = 0)
	{
		$note = (string) $this->input->post('review_note', TRUE);
		$user = (string) $this->session->userdata('user');
		$result = $this->website_measurement->reject((int) $id, $user, $note);
		$this->session->set_flashdata($result['ok'] ? 'suss' : 'error', $result['ok'] ? 'rejected' : 'failed');
		redirect('Website/submissions');
	}

	public function categories()
	{
		$categories = $this->website_model->has_tables()
			? $this->website_model->list_categories(FALSE)
			: array();
		$edit_id = (int) $this->input->get('edit');
		$edit_category = NULL;
		if ($edit_id > 0 && $this->website_model->has_tables())
		{
			$edit_category = $this->website_model->get_category($edit_id);
		}
		$data = array(
			'table_ready' => $this->website_model->has_tables(),
			'settings' => $this->website_model->get_settings(),
			'counts' => $this->website_model->has_tables() ? $this->website_model->dashboard_counts() : array(),
			'categories' => $categories,
			'edit_category' => $edit_category,
			'active_tab' => 'categories',
		);
		$this->load->view('website_manage', $data);
	}

	public function category_save()
	{
		if ( ! $this->website_model->has_tables())
		{
			redirect('Website');
			return;
		}
		$id = (int) $this->input->post('id');
		$name_en = trim((string) $this->input->post('name_en', TRUE));
		if ($name_en === '')
		{
			$this->session->set_flashdata('error', 'category_name_required');
			redirect('Website/categories');
			return;
		}
		$slug = trim((string) $this->input->post('slug', TRUE));
		if ($slug === '')
		{
			$slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name_en));
			$slug = trim($slug, '-');
		}
		if ($slug === '')
		{
			$this->session->set_flashdata('error', 'category_slug_required');
			redirect('Website/categories');
			return;
		}
		if ($this->website_model->category_slug_exists($slug, $id))
		{
			$this->session->set_flashdata('error', 'category_slug_taken');
			redirect('Website/categories'.($id > 0 ? '?edit='.$id : ''));
			return;
		}
		$row = array(
			'slug' => $slug,
			'name_en' => $name_en,
			'name_ur' => trim((string) $this->input->post('name_ur', FALSE)),
			'description_en' => trim((string) $this->input->post('description_en', FALSE)),
			'description_ur' => trim((string) $this->input->post('description_ur', FALSE)),
			'image_url' => trim((string) $this->input->post('image_url', TRUE)),
			'sort_order' => (int) $this->input->post('sort_order'),
			'is_active' => $this->input->post('is_active') ? 1 : 0,
		);
		$saved_id = $this->website_model->save_category($row, $id);
		$this->session->set_flashdata($saved_id > 0 ? 'suss' : 'error', $saved_id > 0 ? 'category_saved' : 'save_failed');
		redirect('Website/categories');
	}

	public function category_toggle($id = 0)
	{
		$id = (int) $id;
		if ($id > 0 && $this->website_model->has_tables())
		{
			$category = $this->website_model->get_category($id);
			if ($category)
			{
				$next = empty($category['is_active']) ? 1 : 0;
				$this->website_model->save_category(array('is_active' => $next), $id);
				$this->session->set_flashdata('suss', $next ? 'category_activated' : 'category_deactivated');
			}
		}
		redirect('Website/categories');
	}

	public function category_delete($id = 0)
	{
		$id = (int) $id;
		if ($id > 0 && $this->website_model->has_tables())
		{
			$used = $this->website_model->count_products_in_category($id);
			if ($used > 0)
			{
				$this->session->set_flashdata('error', 'category_has_products');
				redirect('Website/categories');
				return;
			}
			$this->db->where('id', $id)->delete('web_categories');
			$this->session->set_flashdata('suss', 'category_deleted');
		}
		redirect('Website/categories');
	}

	public function products()
	{
		$products = $this->website_model->has_tables()
			? $this->website_model->list_products(array('include_inactive' => TRUE, 'limit' => 200))
			: array();
		$categories = $this->website_model->has_tables() ? $this->website_model->list_categories(FALSE) : array();
		$edit_id = (int) $this->input->get('edit');
		$edit_product = NULL;
		$edit_image_url = '';
		if ($edit_id > 0 && $this->website_model->has_tables())
		{
			$edit_product = $this->website_model->get_product($edit_id);
			if ($edit_product)
			{
				$imgs = $this->website_model->get_product_images($edit_id);
				$edit_image_url = $imgs ? (string) $imgs[0]['image_url'] : '';
			}
		}
		$data = array(
			'table_ready' => $this->website_model->has_tables(),
			'settings' => $this->website_model->get_settings(),
			'counts' => $this->website_model->has_tables() ? $this->website_model->dashboard_counts() : array(),
			'products' => $products,
			'categories' => $categories,
			'edit_product' => $edit_product,
			'edit_image_url' => $edit_image_url,
			'active_tab' => 'products',
		);
		$this->load->view('website_manage', $data);
	}

	public function product_save()
	{
		if ( ! $this->website_model->has_tables())
		{
			redirect('Website');
			return;
		}
		$id = (int) $this->input->post('id');
		$name_en = trim((string) $this->input->post('name_en', TRUE));
		if ($name_en === '')
		{
			$this->session->set_flashdata('error', 'product_name_required');
			redirect('Website/products');
			return;
		}
		$slug = trim((string) $this->input->post('slug', TRUE));
		if ($slug === '' && $name_en !== '')
		{
			$slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name_en));
			$slug = trim($slug, '-');
		}
		$category_id = (int) $this->input->post('category_id');
		$row = array(
			'category_id' => $category_id > 0 ? $category_id : NULL,
			'slug' => $slug,
			'sku' => trim((string) $this->input->post('sku', TRUE)),
			'name_en' => $name_en,
			'name_ur' => trim((string) $this->input->post('name_ur', FALSE)),
			'short_desc_en' => trim((string) $this->input->post('short_desc_en', TRUE)),
			'description_en' => trim((string) $this->input->post('description_en', FALSE)),
			'price' => (float) $this->input->post('price'),
			'is_active' => $this->input->post('is_active') ? 1 : 0,
			'is_featured' => $this->input->post('is_featured') ? 1 : 0,
			'updated_at' => date('Y-m-d H:i:s'),
		);
		if ($id > 0)
		{
			$this->db->where('id', $id)->update('web_products', $row);
		}
		else
		{
			$row['created_at'] = date('Y-m-d H:i:s');
			$this->db->insert('web_products', $row);
			$id = (int) $this->db->insert_id();
		}
		$image_url = trim((string) $this->input->post('image_url', TRUE));
		if ($image_url !== '' && $id > 0)
		{
			$existing = $this->website_model->get_product_images($id);
			if ($existing)
			{
				$this->db->where('id', (int) $existing[0]['id'])->update('web_product_images', array('image_url' => $image_url));
			}
			else
			{
				$this->db->insert('web_product_images', array(
					'product_id' => $id,
					'image_url' => $image_url,
					'is_primary' => 1,
					'sort_order' => 0,
				));
			}
		}
		$this->session->set_flashdata('suss', 'product_saved');
		redirect('Website/products');
	}

	public function product_toggle($id = 0)
	{
		$id = (int) $id;
		if ($id > 0 && $this->website_model->has_tables())
		{
			$product = $this->website_model->get_product($id);
			if ($product)
			{
				$next = empty($product['is_active']) ? 1 : 0;
				$this->db->where('id', $id)->update('web_products', array(
					'is_active' => $next,
					'updated_at' => date('Y-m-d H:i:s'),
				));
				$this->session->set_flashdata('suss', $next ? 'product_activated' : 'product_deactivated');
			}
		}
		redirect('Website/products');
	}

	public function product_delete($id = 0)
	{
		$id = (int) $id;
		if ($id > 0 && $this->website_model->has_tables())
		{
			$this->db->where('product_id', $id)->delete('web_product_images');
			$this->db->where('id', $id)->delete('web_products');
			$this->session->set_flashdata('suss', 'product_deleted');
		}
		redirect('Website/products');
	}

	public function orders()
	{
		$status = trim((string) $this->input->get('status', TRUE));
		$orders = $this->website_model->has_tables()
			? $this->website_model->list_orders(array('status' => $status, 'q' => trim((string) $this->input->get('q', TRUE))), 100, 0)
			: array();
		$data = array(
			'table_ready' => $this->website_model->has_tables(),
			'settings' => $this->website_model->get_settings(),
			'counts' => $this->website_model->has_tables() ? $this->website_model->dashboard_counts() : array(),
			'orders' => $orders,
			'filter_status' => $status,
			'active_tab' => 'orders',
		);
		$this->load->view('website_manage', $data);
	}

	public function order_status($id = 0)
	{
		$status = trim((string) $this->input->post('status', TRUE));
		$allowed = array('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled');
		if (in_array($status, $allowed, TRUE))
		{
			$this->website_model->update_order((int) $id, array('status' => $status));
			$this->session->set_flashdata('suss', 'order_updated');
		}
		redirect('Website/orders');
	}

	public function guides()
	{
		$guides = $this->website_model->has_tables() ? $this->website_model->get_guides(FALSE) : array();
		$data = array(
			'table_ready' => $this->website_model->has_tables(),
			'settings' => $this->website_model->get_settings(),
			'counts' => $this->website_model->has_tables() ? $this->website_model->dashboard_counts() : array(),
			'guides' => $guides,
			'catalog' => $this->website_measurement->field_catalog(),
			'active_tab' => 'guides',
		);
		$this->load->view('website_manage', $data);
	}

	public function guide_save()
	{
		if ( ! $this->website_model->has_tables())
		{
			redirect('Website');
			return;
		}
		$field_key = trim((string) $this->input->post('field_key', TRUE));
		if ($field_key === '')
		{
			redirect('Website/guides');
			return;
		}
		$row = array(
			'field_key' => $field_key,
			'garment' => trim((string) $this->input->post('garment', TRUE)),
			'label_en' => trim((string) $this->input->post('label_en', TRUE)),
			'label_ur' => trim((string) $this->input->post('label_ur', FALSE)),
			'instruction_en' => trim((string) $this->input->post('instruction_en', FALSE)),
			'instruction_ur' => trim((string) $this->input->post('instruction_ur', FALSE)),
			'example_value' => trim((string) $this->input->post('example_value', TRUE)),
			'video_url' => trim((string) $this->input->post('video_url', TRUE)),
			'is_active' => $this->input->post('is_active') ? 1 : 0,
			'sort_order' => (int) $this->input->post('sort_order'),
		);
		$exists = $this->db->where('field_key', $field_key)->get('web_measurement_guides')->row_array();
		if ($exists)
		{
			$this->db->where('id', (int) $exists['id'])->update('web_measurement_guides', $row);
		}
		else
		{
			$this->db->insert('web_measurement_guides', $row);
		}
		$this->session->set_flashdata('suss', 'guide_saved');
		redirect('Website/guides');
	}
}
