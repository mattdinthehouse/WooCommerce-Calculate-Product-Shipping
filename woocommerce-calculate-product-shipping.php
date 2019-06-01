<?php
/**
 * Plugin Name: WooCommerce Calculate Product Shipping
 * Plugin URI: 
 * Description: Calculate shipping for the current product
 * Author: https://MattDwyer.cool
 * Author URI: https://mattdwyer.cool
 * Version: 0.1
 * License: GPL2
 * Text Domain: WCCPS
 *
 */

// Exit if accessed directly
if(!defined('ABSPATH')) exit;

define('WCCPS_FILE', __FILE__);
define('WCCPS_DIR',  dirname(WCCPS_FILE));
define('WCCPS_URL',  plugins_url('', WCCPS_FILE));

define('WCCPS_VERSION', '0.1');

require_once WCCPS_DIR.'/includes/class-WCCPS.php';

function WCCPS() {
	static $instance = null;

	if(is_null($instance)) {
		if(class_exists('WooCommerce')) {
			$instance = new WCCPS;
		}
	}

	return $instance;
}
add_action('plugins_loaded', 'WCCPS');