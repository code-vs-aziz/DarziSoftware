<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Server-side WhatsApp / SMS HTTP dispatch (Meta Cloud API + generic gateway).
 */
class Whatsapp_api_client
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->helper('url');
	}

	public function is_cloud_configured()
	{
		return dz_env_bool('WHATSAPP_CLOUD_ENABLED', FALSE)
			&& trim((string) dz_env('WHATSAPP_CLOUD_TOKEN', '')) !== ''
			&& trim((string) dz_env('WHATSAPP_CLOUD_PHONE_NUMBER_ID', '')) !== '';
	}

	public function is_http_gateway_configured()
	{
		return trim((string) dz_env('MSG_HTTP_URL', '')) !== '';
	}

	public function is_configured()
	{
		return $this->is_cloud_configured() || $this->is_http_gateway_configured();
	}

	/**
	 * @param string $phone digits e.g. 923001234567
	 * @param string $message
	 * @param string $event_type optional for template mapping
	 * @return array{ok:bool, channel?:string, error?:string, response?:string}
	 */
	public function send($phone, $message, $event_type = '')
	{
		$phone = preg_replace('/\D+/', '', (string) $phone);
		$message = trim((string) $message);
		if ($phone === '' || $message === '')
		{
			return array('ok' => FALSE, 'error' => 'invalid_payload');
		}

		if ($this->is_cloud_configured())
		{
			$cloud = $this->send_cloud($phone, $message, $event_type);
			if (!empty($cloud['ok']))
			{
				return $cloud;
			}
			if (!$this->is_http_gateway_configured())
			{
				return $cloud;
			}
		}

		if ($this->is_http_gateway_configured())
		{
			return $this->send_http_gateway($phone, $message);
		}

		return array('ok' => FALSE, 'error' => 'not_configured');
	}

	protected function send_cloud($phone, $message, $event_type)
	{
		$token = trim((string) dz_env('WHATSAPP_CLOUD_TOKEN', ''));
		$phone_number_id = trim((string) dz_env('WHATSAPP_CLOUD_PHONE_NUMBER_ID', ''));
		$version = trim((string) dz_env('WHATSAPP_CLOUD_API_VERSION', 'v21.0'));
		$use_template = dz_env_bool('WHATSAPP_CLOUD_USE_TEMPLATE', FALSE);
		$template_name = $this->template_name_for_event($event_type);

		$url = 'https://graph.facebook.com/'.$version.'/'.$phone_number_id.'/messages';

		if ($use_template && $template_name !== '')
		{
			$payload = $this->build_template_payload($phone, $message, $event_type, $template_name);
		}
		else
		{
			$payload = array(
				'messaging_product' => 'whatsapp',
				'recipient_type' => 'individual',
				'to' => $phone,
				'type' => 'text',
				'text' => array(
					'preview_url' => FALSE,
					'body' => $message,
				),
			);
		}

		$response = $this->http_json($url, $payload, array(
			'Authorization: Bearer '.$token,
		));

		if (!empty($response['ok']))
		{
			return array(
				'ok' => TRUE,
				'channel' => 'whatsapp_cloud',
				'response' => isset($response['body']) ? (string) $response['body'] : '',
			);
		}

		return array(
			'ok' => FALSE,
			'error' => isset($response['error']) ? (string) $response['error'] : 'cloud_failed',
			'response' => isset($response['body']) ? (string) $response['body'] : '',
		);
	}

	protected function build_template_payload($phone, $message, $event_type, $template_name)
	{
		$lang = trim((string) dz_env('WHATSAPP_CLOUD_TEMPLATE_LANG', 'en'));
		$params = $this->template_parameters($event_type, $message);

		$components = array();
		if (!empty($params))
		{
			$body_params = array();
			foreach ($params as $p)
			{
				$body_params[] = array('type' => 'text', 'text' => (string) $p);
			}
			$components[] = array(
				'type' => 'body',
				'parameters' => $body_params,
			);
		}

		$template = array(
			'name' => $template_name,
			'language' => array('code' => $lang),
		);
		if (!empty($components))
		{
			$template['components'] = $components;
		}

		return array(
			'messaging_product' => 'whatsapp',
			'to' => $phone,
			'type' => 'template',
			'template' => $template,
		);
	}

	protected function template_name_for_event($event_type)
	{
		$map = array(
			'order_booked' => 'WHATSAPP_CLOUD_TEMPLATE_ORDER_BOOKED',
			'order_ready' => 'WHATSAPP_CLOUD_TEMPLATE_ORDER_READY',
			'payment_reminder' => 'WHATSAPP_CLOUD_TEMPLATE_PAYMENT',
			'delivery_confirm' => 'WHATSAPP_CLOUD_TEMPLATE_DELIVERY',
		);
		$event_type = (string) $event_type;
		if (isset($map[$event_type]))
		{
			$name = trim((string) dz_env($map[$event_type], ''));
			if ($name !== '')
			{
				return $name;
			}
		}

		return trim((string) dz_env('WHATSAPP_CLOUD_TEMPLATE_NAME', ''));
	}

	protected function template_parameters($event_type, $message)
	{
		$pipe = strpos($message, '|') !== FALSE;
		if ($pipe)
		{
			return array_map('trim', explode('|', $message, 3));
		}

		if ($event_type === 'order_booked')
		{
			return array($message);
		}

		return array($message);
	}

	protected function send_http_gateway($phone, $message)
	{
		$url_template = trim((string) dz_env('MSG_HTTP_URL', ''));
		$method = strtoupper(trim((string) dz_env('MSG_HTTP_METHOD', 'POST')));
		$phone_field = trim((string) dz_env('MSG_HTTP_PHONE_FIELD', 'phone'));
		$message_field = trim((string) dz_env('MSG_HTTP_MESSAGE_FIELD', 'message'));

		if (strpos($url_template, '{phone}') !== FALSE || strpos($url_template, '{message}') !== FALSE)
		{
			$url = str_replace(
				array('{phone}', '{message}'),
				array(rawurlencode($phone), rawurlencode($message)),
				$url_template
			);
			$response = $this->http_request($method, $url, NULL, $this->auth_headers());
		}
		else
		{
			$payload = array(
				$phone_field => $phone,
				$message_field => $message,
			);
			$response = $this->http_request($method, $url_template, $payload, $this->auth_headers());
		}

		if (!empty($response['ok']))
		{
			return array(
				'ok' => TRUE,
				'channel' => 'http_gateway',
				'response' => isset($response['body']) ? (string) $response['body'] : '',
			);
		}

		return array(
			'ok' => FALSE,
			'error' => isset($response['error']) ? (string) $response['error'] : 'http_failed',
			'response' => isset($response['body']) ? (string) $response['body'] : '',
		);
	}

	protected function auth_headers()
	{
		$headers = array();
		$key_header = trim((string) dz_env('MSG_HTTP_AUTH_HEADER', ''));
		$key_value = trim((string) dz_env('MSG_HTTP_AUTH_VALUE', ''));
		if ($key_header !== '' && $key_value !== '')
		{
			$headers[] = $key_header.': '.$key_value;
		}
		return $headers;
	}

	protected function http_json($url, array $payload, array $extra_headers = array())
	{
		$headers = array_merge(array('Content-Type: application/json'), $extra_headers);
		return $this->http_request('POST', $url, $payload, $headers, TRUE);
	}

	protected function http_request($method, $url, $payload = NULL, array $headers = array(), $json = FALSE)
	{
		if (!function_exists('curl_init'))
		{
			return array('ok' => FALSE, 'error' => 'curl_missing', 'body' => '');
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);

		if ($method === 'POST')
		{
			curl_setopt($ch, CURLOPT_POST, TRUE);
			if ($payload !== NULL)
			{
				$body = $json ? json_encode($payload) : http_build_query($payload);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
			}
		}

		if (!empty($headers))
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		$body = curl_exec($ch);
		$errno = curl_errno($ch);
		$http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($errno !== 0)
		{
			return array('ok' => FALSE, 'error' => 'curl_error_'.$errno, 'body' => (string) $body);
		}

		if ($http_code >= 200 && $http_code < 300)
		{
			return array('ok' => TRUE, 'body' => (string) $body);
		}

		return array('ok' => FALSE, 'error' => 'http_'.$http_code, 'body' => (string) $body);
	}
}
