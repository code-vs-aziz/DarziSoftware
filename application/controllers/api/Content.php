<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'core/Api_Controller.php';

/**
 * Public CMS/content endpoints (indexable marketing data).
 * Routes: /api/content/{method}
 */
class Content extends Api_Controller
{
	public function __construct()
	{
		parent::__construct();
		// Marketing content may be indexed by the Next.js layer; API itself stays noindex.
		$this->ensure_schema();
	}

	public function home()
	{
		$settings = $this->website_model->get_settings();
		$sections = $this->website_model->get_home_sections(TRUE);
		$faqs = $this->website_model->get_faqs(TRUE);
		$biz = array();
		if ($this->db->table_exists('business_configuration'))
		{
			$this->load->model('Business_config_model');
			$biz = $this->Business_config_model->get_config();
		}

		return $this->ok(array(
			'settings' => array(
				'site_name' => $settings['site_name'],
				'tagline_en' => $settings['tagline_en'],
				'tagline_ur' => $settings['tagline_ur'],
				'about_en' => $settings['about_en'],
				'about_ur' => $settings['about_ur'],
				'contact_phone' => $settings['contact_phone'] !== '' ? $settings['contact_phone'] : (isset($biz['phone']) ? $biz['phone'] : ''),
				'contact_whatsapp' => $settings['contact_whatsapp'],
				'contact_email' => $settings['contact_email'],
				'contact_address_en' => $settings['contact_address_en'],
				'contact_address_ur' => $settings['contact_address_ur'] !== '' ? $settings['contact_address_ur'] : (isset($biz['address_urdu']) ? $biz['address_urdu'] : ''),
				'seo_title_default' => $settings['seo_title_default'],
				'seo_description_default' => $settings['seo_description_default'],
				'og_image_url' => $settings['og_image_url'],
				'feature_shop' => (int) $settings['feature_shop'],
				'feature_measurements' => (int) $settings['feature_measurements'],
				'shop_name' => isset($biz['location_name']) ? $biz['location_name'] : '',
			),
			'sections' => $sections,
			'faqs' => $faqs,
		));
	}

	public function page($slug = '')
	{
		$slug = trim((string) $slug);
		if ($slug === '')
		{
			return $this->fail('not_found', 404);
		}
		$page = $this->website_model->get_page_by_slug($slug);
		if ( ! $page)
		{
			return $this->fail('not_found', 404);
		}
		return $this->ok(array('page' => $page));
	}

	public function health()
	{
		return $this->ok(array(
			'service' => 'darzi-baba-api',
			'schema' => $this->website_model->has_tables(),
			'time' => date('c'),
		));
	}
}
