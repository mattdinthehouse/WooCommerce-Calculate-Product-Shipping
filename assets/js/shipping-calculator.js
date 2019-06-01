/* global wccps_shipping_calculator_params */
jQuery(function($) {

	// wccps_shipping_calculator_params is required to continue, ensure the object exists
	if(typeof wccps_shipping_calculator_params === 'undefined') {
		return false;
	}

	var cart_shipping = {

		/**
		 * Initialize event handlers and UI state.
		 */
		init: function() {
			$(document)
				.on(
					'click',
					'.shipping-calculator-button',
					this.toggle_shipping
				);
		},

		/**
		 * Toggle Shipping Calculator panel
		 */
		toggle_shipping: function() {
			$('.shipping-calculator-form').slideToggle('slow');
			$(document.body).trigger('country_to_state_changed' ); // Trigger select2 to load.
			return false;
		},
	};

	cart_shipping.init();
});