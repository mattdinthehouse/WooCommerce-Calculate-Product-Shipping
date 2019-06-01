<?php

// Exit if accessed directly
if(!defined('ABSPATH')) exit;

if(!function_exists('wccps_template_shipping_calculator')) {

	function wccps_template_shipping_calculator() {
		if(!empty($_POST['calculate_product_shipping'])) {
			try {
				// Fetch the location information
				$country  = wc_clean($_POST['calc_shipping_country']);
				$state    = wc_clean(isset($_POST['calc_shipping_state']) ? $_POST['calc_shipping_state'] : '');
				$postcode = apply_filters('woocommerce_shipping_calculator_enable_postcode', true) ? wc_clean($_POST['calc_shipping_postcode']) : '';
				$city     = apply_filters('woocommerce_shipping_calculator_enable_city', true) ? wc_clean($_POST['calc_shipping_city']) : '';

				WCCPS::set_customer_location($country, $state, $postcode, $city);

				// Get shipping rates for the given product
				$product_id = (!empty($_POST['product_id']) ? absint($_POST['product_id']) : 0);
				$quantity   = (!empty($_POST['quantity']) ? absint($_POST['quantity']) : 1);

				$rates = WCCPS::calculate_shipping($product_id, $quantity);

				if(empty($rates)) {
					throw new Exception('No shipping methods were found.');
				}
				else {
					$message = wccps_template_rates_html($rates);
					wc_add_notice($message, 'success');
				}
			}
			catch(Exception $e) {
				wc_add_notice($e->getMessage(), 'error');
			}
		}

		wc_get_template('single-product/shipping-calculator.php');
	}
}

if(!function_exists('wccps_template_rates_html')) {

	function wccps_template_rates_html($rates) {
		$rates_html = array();
		foreach($rates as $rate) {
			$rates_html[] = '<strong>'.$rate->get_label().'</strong>: '.wc_price($rate->cost);
		}

		$message = '<strong>'.get_the_title().'</strong> with '.implode(', ', $rates_html);

		return $message;
	}
}