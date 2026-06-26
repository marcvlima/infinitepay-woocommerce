<?php

namespace InfinitePay\WooCommerce\Webhooks;

use InfinitePay\WooCommerce\Api\PaymentCheckEndpoint;
use InfinitePay\WooCommerce\Logger;
use InfinitePay\WooCommerce\Order\OrderHelper;
use WC_Order;
use WP_Error;

defined( 'ABSPATH' ) || exit;

class WebhookValidator {

	private $payment_check;
	private $logger;
	private $handle;

	public function __construct( PaymentCheckEndpoint $payment_check, Logger $logger, string $handle ) {
		$this->payment_check = $payment_check;
		$this->logger        = $logger;
		$this->handle        = $handle;
	}

	/**
	 * @return WC_Order|WP_Error
	 */
	public function validate( array $payload ) {
		if ( empty( $payload['order_nsu'] ) ) {
			return new WP_Error( 'infinitepay_missing_nsu', 'Missing order_nsu in webhook payload.' );
		}

		$order = OrderHelper::find_order_by_nsu( $payload['order_nsu'] );
		if ( ! $order ) {
			return new WP_Error( 'infinitepay_order_not_found', 'Order not found for NSU: ' . $payload['order_nsu'] );
		}

		$method = $order->get_payment_method();
		if ( ! in_array( $method, [ 'infinitepay_checkout', 'infinitepay_pix' ], true ) ) {
			return new WP_Error( 'infinitepay_wrong_gateway', 'Order payment method is not InfinitePay.' );
		}

		// Idempotency — skip already processed orders.
		if ( in_array( $order->get_status(), [ 'processing', 'completed' ], true ) ) {
			return new WP_Error( 'infinitepay_already_paid', 'Order already processed.' );
		}

		$transaction_nsu = isset( $payload['transaction_nsu'] ) ? (string) $payload['transaction_nsu'] : '';
		$slug            = isset( $payload['invoice_slug'] ) ? (string) $payload['invoice_slug'] : '';

		$check = $this->payment_check->check( $this->handle, $payload['order_nsu'], $transaction_nsu, $slug );

		if ( is_wp_error( $check ) ) {
			return $check;
		}

		if ( empty( $check['paid'] ) ) {
			return new WP_Error( 'infinitepay_not_paid', 'Payment check returned not paid.' );
		}

		$order_total_cents = (int) round( $order->get_total() * 100 );
		$paid_amount       = (int) $check['paid_amount'];

		// Allow 1 cent tolerance for rounding.
		if ( $paid_amount < ( $order_total_cents - 1 ) ) {
			return new WP_Error(
				'infinitepay_amount_mismatch',
				sprintf( 'Amount mismatch: expected %d cents, received %d cents.', $order_total_cents, $paid_amount )
			);
		}

		return $order;
	}
}
