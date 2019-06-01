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
		if(is_product() || (!empty($post->post_content) && strstr($post->post_content, '[product_page'))) {
			wp_enqueue_script('selectWoo');
			wp_enqueue_style('select2');

			wp_register_script('wccps-shipping-calculator', WCCPS_URL.'/assets/js/shipping-calculator.js', array('jquery', 'woocommerce', 'wc-country-select', 'wc-address-i18n'), WCCPS_VERSION, true);
			wp_enqueue_script('wccps-shipping-calculator');
			wp_localize_script('wccps-shipping-calculator', 'wccps_shipping_calculator_params', apply_filters('wccps_shipping_calculator_params', array(
			)));
		}
	}
}