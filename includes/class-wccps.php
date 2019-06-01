<?php

// Exit if accessed directly
if(!defined('ABSPATH')) exit;

class WCCPS {

	public function __construct() {
		$this->includes();
		$this->hooks();
	}

	private function includes() {
		require_once WCCPS_DIR.'/includes/class-wccps-ajax.php';
		require_once WCCPS_DIR.'/includes/class-wccps-frontend.php';

		$this->frontend = new WCCPS_Frontend();

		WCCPS_AJAX::init();
	}

	private function hooks() {
	}

	public static function set_customer_location($country, $state, $postcode, $city) {
		// Based off WC_Shortcode_Cart::calculate_shipping() in WC v3.6.2

		if($postcode && !WC_Validation::is_postcode($postcode, $country)) {
			throw new Exception(__('Please enter a valid postcode/ZIP.', 'woocommerce'));
		}
		else if($postcode) {
			$postcode = wc_format_postcode($postcode, $country);
		}

		if($country) {
			WC()->customer->set_location($country, $state, $postcode, $city);
			WC()->customer->set_shipping_location($country, $state, $postcode, $city);
		}
		else {
			WC()->customer->set_to_base();
			WC()->customer->set_shipping_to_base();
		}
	}

	public static function calculate_shipping($product_id, $quantity = 1, $variation_id = 0) {
		// Figure out what product and variation to work with
		if('product_variation' == get_post_type($product_id)) {
			$variation_id = $product_id;
			$product_id   = wp_get_post_parent_id($variation_id);
		}

		$product   = wc_get_product($product_id);
		$variation = array();
		if(!$product || !$product->exists()) {
			throw new Exception('Invalid product ID: '.$product_id);
		}

		// Sanitise the quantity
		$quantity = max(1, $quantity);

		// Generate the cart contents
		$cart = self::generate_cart_contents($product, $product_id, $variation_id, $quantity);

		// Generate the shipping package
		$package = self::generate_shipping_package($cart, $product->get_price() * $quantity);

		// Calculate shipping
		$rates = self::get_rates_for_package($package);

		return $rates;
	}

	private static function generate_cart_contents($product, $product_id, $variation_id, $quantity) {
		// Based off WC_Cart::add_to_cart() in WC v3.6.2

		$cart = array();

		$variation      = array();
		$cart_item_data = array();
		$cart_item_key  = WC()->cart->generate_cart_id($product_id, $variation_id, $variation, $cart_item_data);

		$cart[] = array(
			'key'          => $cart_item_key,
			'product_id'   => $product_id,
			'variation_id' => $variation_id,
			'variation'    => $variation,
			'quantity'     => $quantity,
			'data'         => $product,
			'data_hash'    => wc_get_cart_item_data_hash($product),
		);

		return $cart;
	}

	private static function generate_shipping_package($cart_items, $subtotal) {
		// Based off WC_Cart::get_shipping_packages() in WC v3.6.2

		return array(
			'contents'        => $cart_items,
			'contents_cost'   => $subtotal,
			'applied_coupons' => array(),
			'user'            => array(
				'ID' => get_current_user_id(),
			),
			'destination'     => array(
				'country'   => WC()->cart->get_customer()->get_shipping_country(),
				'state'     => WC()->cart->get_customer()->get_shipping_state(),
				'postcode'  => WC()->cart->get_customer()->get_shipping_postcode(),
				'city'      => WC()->cart->get_customer()->get_shipping_city(),
				'address'   => WC()->cart->get_customer()->get_shipping_address(),
				'address_1' => WC()->cart->get_customer()->get_shipping_address(),
				'address_2' => WC()->cart->get_customer()->get_shipping_address_2(),
			),
			'cart_subtotal'   => $subtotal,
		);
	}

	private static function get_rates_for_package($package) {
		// Based off WC_Shipping::is_package_shippable() in WC v3.6.2

		$is_shippable = false;

		if(empty($package['destination']['country'])) {
			$is_shippable = true;
		}

		$allowed_countries = array_keys(WC()->countries->get_shipping_countries());
		$is_shippable      = in_array($package['destination']['country'], $allowed_countries, true);

		if(!$is_shippable) {
			throw new Exception('Unfortunately this package is not shippable.');
		}


		// Based off WC_Shipping::calculate_shipping_for_package() in WC v3.6.2 but with transients instead of session

		$rates = array();

		$package_to_hash = $package;
		foreach($package_to_hash['contents'] as $item_id => $item) {
			unset($package_to_hash['contents'][$item_id]['data']);
		}

		$transient_key = 'wccps_shipping_rates_'.md5(wp_json_encode($package_to_hash));
		$stored_rate   = get_transient($transient_key);

		if(!is_array($stored_rate)) {
			foreach(WC()->shipping->load_shipping_methods($package) as $shipping_method) {
				if(!$shipping_method->supports('shipping-zones') || $shipping_method->get_instance_id()) {
					$rates = $rates + $shipping_method->get_rates_for_package($package); // + instead of array_merge maintains numeric keys
				}
			}

			$rates = apply_filters('woocommerce_package_rates', $rates, $package);

			set_transient($transient_key, $rates, HOUR_IN_SECONDS * 2);
		}
		else {
			$rates = $stored_rate;
		}

		return $rates;
	}
}