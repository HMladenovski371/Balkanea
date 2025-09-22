<?php
/**
 * Plugin Name: Payment Gateway for Cpay with WooCommerce
 * Plugin URI: https://wordpress.org/plugins/payment-gateway-for-cpay-with-woocommerce/
 * Description: Implements the Cpay bank payment gateway.
 * Author: Webpigment
 * Author URI: https://www.webpigment.com/
 * Version: 1.2
 * Text Domain: payment-gateway-for-cpay-with-woocommerce
 * Domain Path: /i18n/languages/
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-cpay-Gateway
 * @author    Mitko Kockovski
 * @category  Admin
 * @copyright Copyright (c) Mitko Kockovski
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */
defined( 'ABSPATH' ) or exit;

// Make sure WooCommerce is active.
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	return;
}

/**
 * Add the gateway to WC Available Gateways
 *
 * @since 1.0.0
 * @param array $gateways all available WC gateways.
 * @return array $gateways all WC gateways + cpay gateway
 */
function wc_cpay_add_to_gateways( $gateways ) {
	require_once realpath( dirname( __FILE__ ) ) . '/class-wc-payment-gateway-for-cpay-with-woocommerce.php';
	$gateways[] = 'Wc_Payment_Gateway_For_Cpay_With_Woocommerce';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'wc_cpay_add_to_gateways' );
