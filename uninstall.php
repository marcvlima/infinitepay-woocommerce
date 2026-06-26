<?php

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'woocommerce_infinitepay_checkout_settings' );
delete_option( 'woocommerce_infinitepay_pix_settings' );

wp_clear_scheduled_hook( 'infinitepay_check_pending_payments' );

if ( apply_filters( 'infinitepay_wc_uninstall_delete_order_meta', false ) ) {
	global $wpdb;
	$keys = [
		'_infinitepay_checkout_url',
		'_infinitepay_order_nsu',
		'_infinitepay_transaction_nsu',
		'_infinitepay_receipt_url',
		'_infinitepay_capture_method',
		'_infinitepay_installments',
		'_infinitepay_invoice_slug',
		'_infinitepay_payment_checked_at',
	];
	foreach ( $keys as $key ) {
		$wpdb->delete( $wpdb->postmeta, [ 'meta_key' => $key ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}
}
