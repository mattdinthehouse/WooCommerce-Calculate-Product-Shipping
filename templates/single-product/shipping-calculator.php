<?php
/**
 * Shipping calculator for the current product
 *
 * Based off woocommerce/templates/cart/shipping-calculator.php v3.5.0
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/shipping-calculator.php.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @version     0.1
 */

if(!defined('ABSPATH')) {
	exit;
}

do_action('wccps_before_shipping_calculator');

?>

<form class="woocommerce-shipping-calculator" action="<?php echo esc_url(get_permalink()); ?>" method="post">
	
	<?php printf( '<a href="#" class="shipping-calculator-button">%s</a>', esc_html( ! empty( $button_text ) ? $button_text : __( 'Calculate shipping', 'woocommerce' ) ) ); ?>

	<?php wc_print_notices(); ?>

	<section class="shipping-calculator-form" style="display:none;">
		<?php if(apply_filters('woocommerce_shipping_calculator_enable_country', true)) { ?>
			<p class="form-row form-row-wide" id="calc_shipping_country_field">
				<select name="calc_shipping_country" id="calc_shipping_country" class="country_to_state country_select" rel="calc_shipping_state">
					<option value=""><?php esc_html_e('Select a country&hellip;', 'woocommerce'); ?></option>
					<?php
						foreach(WC()->countries->get_shipping_countries() as $key => $value) {
							echo '<option value="'.esc_attr($key).'"'.selected(WC()->customer->get_shipping_country(), esc_attr($key), false).'>'.esc_html($value).'</option>';
						}
					?>
				</select>
			</p>
		<?php } ?>

		<?php if(apply_filters('woocommerce_shipping_calculator_enable_state', true)) { ?>
			<p class="form-row form-row-wide" id="calc_shipping_state_field">
				<?php
					$current_cc = WC()->customer->get_shipping_country();
					$current_r  = WC()->customer->get_shipping_state();
					$states     = WC()->countries->get_states($current_cc);

					if(is_array($states) && empty($states)) {
						?>
						<input type="hidden" name="calc_shipping_state" id="calc_shipping_state" placeholder="<?php esc_attr_e('State / County', 'woocommerce'); ?>" />
						<?php
					}
					else if(is_array($states)) {
						?>
						<span>
							<select name="calc_shipping_state" class="state_select" id="calc_shipping_state" data-placeholder="<?php esc_attr_e('State / County', 'woocommerce'); ?>">
								<option value=""><?php esc_html_e('Select an option&hellip;', 'woocommerce'); ?></option>
								<?php
									foreach($states as $ckey => $cvalue) {
										echo '<option value="'.esc_attr($ckey).'" '.selected($current_r, $ckey, false).'>'.esc_html($cvalue).'</option>';
									}
								?>
							</select>
						</span>
						<?php
					} else {
						?>
						<input type="text" class="input-text" value="<?php echo esc_attr($current_r); ?>" placeholder="<?php esc_attr_e('State / County', 'woocommerce'); ?>" name="calc_shipping_state" id="calc_shipping_state" />
						<?php
					}
				?>
			</p>
		<?php } ?>

		<?php if(apply_filters('woocommerce_shipping_calculator_enable_city', true)) { ?>
			<p class="form-row form-row-wide" id="calc_shipping_city_field">
				<input type="text" class="input-text" value="<?php echo esc_attr(WC()->customer->get_shipping_city()); ?>" placeholder="<?php esc_attr_e('City', 'woocommerce'); ?>" name="calc_shipping_city" id="calc_shipping_city" />
			</p>
		<?php } ?>

		<?php if(apply_filters('woocommerce_shipping_calculator_enable_postcode', true)) { ?>
			<p class="form-row form-row-wide" id="calc_shipping_postcode_field">
				<input type="text" class="input-text" value="<?php echo esc_attr(WC()->customer->get_shipping_postcode()); ?>" placeholder="<?php esc_attr_e('Postcode / ZIP', 'woocommerce'); ?>" name="calc_shipping_postcode" id="calc_shipping_postcode" />
			</p>
		<?php } ?>

		<p><button type="submit" name="calculate_product_shipping" value="1" class="button"><?php esc_html_e('Update', 'woocommerce'); ?></button></p>

		<input type="hidden" name="product_id" value="<?php echo esc_attr(get_the_id()); ?>" data-product_id="<?php echo esc_attr(get_the_id()); ?>"/>
		<input type="hidden" name="quantity" value="1"/>
		<?php wp_nonce_field('woocommerce-shipping-calculator', 'woocommerce-shipping-calculator-nonce'); ?>
	</section>
</form>

<?php

do_action('wccps_after_shipping_calculator');
