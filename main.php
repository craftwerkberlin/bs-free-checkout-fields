<?php
/*Plugin Name: bS Free Checkout Fields
Plugin URI: https://bootscore.me/
Description: This plugin reduces the checkput fields and hide payment if product is free and cart 0 in WooCommerce. It is a fork of https://gist.github.com/bekarice/474ab82ab37b8de8617d#file-wc-free-checkout-fields-php
Version: 1.0.1
Author: Bastian Kreiter
Author URI: https://crftwrk.de
License: GPLv2
*/


/**
 * Removes coupon form, order notes, and several billing fields if the checkout doesn't require payment.
 *
 * REQUIRES PHP 5.3+
 *
 * Tutorial: http://skyver.ge/c
 */
function sv_free_checkout_fields() {

	// first, bail if WC isn't active since we're hooked into a general WP hook
	if ( ! function_exists( 'WC' ) ) {
		return;	
	}

	// bail if the cart needs payment, we don't want to do anything
	if ( WC()->cart && WC()->cart->needs_payment() ) {
		return;
	}

	// now continue only if we're at checkout
	// is_checkout() was broken as of WC 3.2 in ajax context, double-check for is_ajax
	// I would check WOOCOMMERCE_CHECKOUT but testing shows it's not set reliably
	if ( function_exists( 'is_checkout' ) && ( is_checkout() || is_ajax() ) ) {

		// remove coupon forms since why would you want a coupon for a free cart??
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

		// Remove the "Additional Info" order notes
		add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );

		// Unset the fields we don't want in a free checkout
		add_filter( 'woocommerce_checkout_fields', function( $fields ) {

			// add or remove billing fields you do not want
			// fields: http://docs.woothemes.com/document/tutorial-customising-checkout-fields-using-actions-and-filters/#section-2
			$billing_keys = array(
				'billing_company',
				'billing_phone',
				'billing_address_1',
				'billing_address_2',
				'billing_city',
				'billing_postcode',
				'billing_country',
				'billing_state',
			);

			// unset each of those unwanted fields
			foreach( $billing_keys as $key ) {
				unset( $fields['billing'][ $key ] );
			}

			return $fields;
		} );
	}

}
add_action( 'wp', 'sv_free_checkout_fields' );



// Show FREE instead  of 0 if price is 0
add_filter( 'woocommerce_get_price_html', 'bootscore_price_free_zero_empty', 9999, 2 );
   
function bootscore_price_free_zero_empty( $price, $product ){
    if ( '' === $product->get_price() || 0 == $product->get_price() ) {
        $price = '<span class="woocommerce-Price-amount amount badge badge-danger">FREE</span>';
    }  
    return $price;
}