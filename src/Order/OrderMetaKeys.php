<?php

namespace InfinitePay\WooCommerce\Order;

defined( 'ABSPATH' ) || exit;

class OrderMetaKeys {
	const CHECKOUT_URL       = '_infinitepay_checkout_url';
	const ORDER_NSU          = '_infinitepay_order_nsu';
	const TRANSACTION_NSU    = '_infinitepay_transaction_nsu';
	const RECEIPT_URL        = '_infinitepay_receipt_url';
	const CAPTURE_METHOD     = '_infinitepay_capture_method';
	const INSTALLMENTS       = '_infinitepay_installments';
	const INVOICE_SLUG       = '_infinitepay_invoice_slug';
	const PAYMENT_CHECKED_AT = '_infinitepay_payment_checked_at';
}
