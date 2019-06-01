<?php

// Exit if accessed directly
if(!defined('ABSPATH')) exit;

class WCCPS_AJAX {

	public static function init() {
		add_action('wp_ajax_woocommerce_shipping_zones_save_changes', array(__CLASS__, 'delete_calculate_rate_transients'), 0);
		add_action('wp_ajax_woocommerce_shipping_zone_add_method', array(__CLASS__, 'delete_calculate_rate_transients'), 0);
		add_action('wp_ajax_woocommerce_shipping_zone_methods_save_changes', array(__CLASS__, 'delete_calculate_rate_transients'), 0);
		add_action('wp_ajax_woocommerce_shipping_zone_methods_save_settings', array(__CLASS__, 'delete_calculate_rate_transients'), 0);
		add_action('wp_ajax_woocommerce_shipping_classes_save_changes', array(__CLASS__, 'delete_calculate_rate_transients'), 0);
	}

	public static function delete_calculate_rate_transients() {
		global $wpdb;

		$transient_names = $wpdb->get_col("
			SELECT option_name FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_wccps_shipping_rates_%';
			");

		foreach($transient_names as $transient_name) {
			$transient_name = substr($transient_name, 11);
			delete_transient($transient_name);
		}
	}
}