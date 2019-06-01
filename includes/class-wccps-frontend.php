<?php

// Exit if accessed directly
if(!defined('ABSPATH')) exit;

class WCCPS_Frontend {

	public function __construct() {
		add_filter('woocommerce_locate_template', array($this, 'locate_template'), 10, 3);

		add_action('after_setup_theme', array($this, 'include_template_functions'), 12);

		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

		add_action('woocommerce_single_product_summary', 'wccps_template_shipping_calculator', 70);
	}

	public function locate_template($template, $template_name, $template_path) {
		if(!file_exists($template)) {
			$plugin_template = WCCPS_DIR.'/templates/'.$template_name;

			if(file_exists($plugin_template)) {
				$template = $plugin_template;
			}
		}

		return $template;
	}

	public function include_template_functions() {
		require_once WCCPS_DIR.'/includes/wccps-template-functions.php';
	}

	public function enqueue_scripts() {
		wp_register_script('wccps-calculate-shipping', WCCPS_URL.'/assets/js/calculate-shipping.js', array('jquery'), WCCPS_VERSION, true);
		wp_enqueue_script('wccps-calculate-shipping');
		wp_localize_script('wccps-calculate-shipping', 'wccps_calculate_shipping_params', apply_filters('wccps_calculate_shipping_params', array(
			'ajax_url'                => WC()->ajax_url(),
			'wc_ajax_url'             => WC_AJAX::get_endpoint('%%endpoint%%'),
			'i18n_calculate_shipping' => esc_attr__('Calculate shipping', 'wccps'),
			'i18n_calculate'          => esc_attr__('Calculate', 'wccps'),
		)));
	}
}