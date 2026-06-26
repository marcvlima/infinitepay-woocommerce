<?php

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Minimal WP/WC stubs for unit tests.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/' );
}
if ( ! defined( 'INFINITEPAY_WC_VERSION' ) ) {
	define( 'INFINITEPAY_WC_VERSION', '1.0.0' );
}
if ( ! defined( 'INFINITEPAY_WC_PATH' ) ) {
	define( 'INFINITEPAY_WC_PATH', dirname( __DIR__ ) . '/' );
}
if ( ! defined( 'INFINITEPAY_WC_URL' ) ) {
	define( 'INFINITEPAY_WC_URL', 'https://example.com/wp-content/plugins/infinitepay-woocommerce/' );
}
if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

// Basic WP function stubs.
if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) {
		return $thing instanceof WP_Error;
	}
}
if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data ) {
		return json_encode( $data );
	}
}
if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = '' ) {
		return $text;
	}
}
if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = '' ) {
		return htmlspecialchars( $text, ENT_QUOTES );
	}
}

require_once __DIR__ . '/stubs/class-wp-error.php';
require_once __DIR__ . '/stubs/class-wc-order.php';
