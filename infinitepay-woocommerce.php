<?php
/**
 * Plugin Name:       InfinitePay for WooCommerce
 * Plugin URI:        https://github.com/marcvlima/infinitepay-woocommerce
 * Description:       Integração do WooCommerce com o Checkout Integrado da InfinitePay via InfiniteTag.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Tested up to:      6.7
 * WC requires at least: 8.0
 * WC tested up to:   9.9
 * Author:            Marcus Lima
 * Author URI:        https://github.com/marcvlima
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       infinitepay-woocommerce
 * Domain Path:       /i18n/languages
 */

defined( 'ABSPATH' ) || exit;

define( 'INFINITEPAY_WC_VERSION', '1.0.0' );
define( 'INFINITEPAY_WC_FILE',    __FILE__ );
define( 'INFINITEPAY_WC_PATH',    plugin_dir_path( __FILE__ ) );
define( 'INFINITEPAY_WC_URL',     plugin_dir_url( __FILE__ ) );

if ( ! file_exists( INFINITEPAY_WC_PATH . 'vendor/autoload.php' ) ) {
	add_action( 'admin_notices', function () {
		echo '<div class="notice notice-error"><p>'
			. esc_html__( 'InfinitePay for WooCommerce: execute "composer install" no diretório do plugin.', 'infinitepay-woocommerce' )
			. '</p></div>';
	} );
	return;
}

require_once INFINITEPAY_WC_PATH . 'vendor/autoload.php';

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use InfinitePay\WooCommerce\Admin\StatusPage;
use InfinitePay\WooCommerce\Api\Client;
use InfinitePay\WooCommerce\Api\PaymentCheckEndpoint;
use InfinitePay\WooCommerce\Blocks\BlockSupport;
use InfinitePay\WooCommerce\Gateways\CheckoutGateway;
use InfinitePay\WooCommerce\Gateways\PixGateway;
use InfinitePay\WooCommerce\Logger;
use InfinitePay\WooCommerce\PaymentRecovery\AdminVerifyButton;
use InfinitePay\WooCommerce\PaymentRecovery\CronChecker;
use InfinitePay\WooCommerce\PaymentRecovery\ReturnHandler;
use InfinitePay\WooCommerce\Webhooks\WebhookHandler;
use InfinitePay\WooCommerce\Webhooks\WebhookValidator;

// Declare HPOS + Blocks compatibility.
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( FeaturesUtil::class ) ) {
		FeaturesUtil::declare_compatibility( 'custom_order_tables', INFINITEPAY_WC_FILE, true );
		FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', INFINITEPAY_WC_FILE, true );
	}
} );

add_action( 'plugins_loaded', function () {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p>'
				. esc_html__( 'InfinitePay for WooCommerce requer o WooCommerce ativo.', 'infinitepay-woocommerce' )
				. '</p></div>';
		} );
		return;
	}

	load_plugin_textdomain( 'infinitepay-woocommerce', false, dirname( plugin_basename( INFINITEPAY_WC_FILE ) ) . '/i18n/languages' );

	$settings = get_option( 'woocommerce_infinitepay_checkout_settings', [] );
	$handle   = isset( $settings['handle'] ) ? (string) $settings['handle'] : '';

	$logger        = new Logger();
	$client        = new Client( $logger );
	$payment_check = new PaymentCheckEndpoint( $client );

	// REST webhook.
	$validator = new WebhookValidator( $payment_check, $logger, $handle );
	$webhook   = new WebhookHandler( $validator, $logger );
	add_action( 'rest_api_init', [ $webhook, 'register_routes' ] );

	// Payment recovery.
	( new ReturnHandler( $payment_check, $logger, $handle ) )->register();
	( new CronChecker( $payment_check, $logger, $handle ) )->register();
	( new AdminVerifyButton( $payment_check, $logger, $handle ) )->register();

	// Admin status page.
	( new StatusPage() )->register();
} );

// Register payment gateways.
add_filter( 'woocommerce_payment_gateways', function ( $gateways ) {
	$gateways[] = CheckoutGateway::class;
	$gateways[] = PixGateway::class;
	return $gateways;
} );

// Register Blocks integration.
add_action( 'woocommerce_blocks_loaded', function () {
	if ( class_exists( '\Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			function ( $registry ) {
				$registry->register( new BlockSupport() );
			}
		);
	}
} );

register_activation_hook( INFINITEPAY_WC_FILE, function () {
	if ( ! wp_next_scheduled( 'infinitepay_check_pending_payments' ) ) {
		wp_schedule_event( time(), 'fifteen_minutes', 'infinitepay_check_pending_payments' );
	}
} );

register_deactivation_hook( INFINITEPAY_WC_FILE, function () {
	wp_clear_scheduled_hook( 'infinitepay_check_pending_payments' );
} );
