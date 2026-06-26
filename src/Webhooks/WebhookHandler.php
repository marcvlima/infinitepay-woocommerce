<?php

namespace InfinitePay\WooCommerce\Webhooks;

use InfinitePay\WooCommerce\Logger;
use InfinitePay\WooCommerce\Order\OrderHelper;
use InfinitePay\WooCommerce\Order\OrderMetaKeys;
use WP_REST_Request;
use WP_REST_Response;

defined( 'ABSPATH' ) || exit;

class WebhookHandler {

	private $validator;
	private $logger;

	public function __construct( WebhookValidator $validator, Logger $logger ) {
		$this->validator = $validator;
		$this->logger    = $logger;
	}

	public function register_routes(): void {
		register_rest_route(
			'infinitepay/v1',
			'/webhook',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'handle' ],
				'permission_callback' => '__return_true',
			]
		);
	}

	public function handle( WP_REST_Request $request ): WP_REST_Response {
		$payload = json_decode( $request->get_body(), true );

		$this->logger->info( 'Webhook received.' );

		if ( ! is_array( $payload ) ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Invalid payload.' ], 400 );
		}

		$order = $this->validator->validate( $payload );

		if ( is_wp_error( $order ) ) {
			$code = $order->get_error_code();
			// Idempotency — already paid is not an error from InfinitePay's perspective.
			$status = 'infinitepay_already_paid' === $code ? 200 : 400;
			$this->logger->warning( 'Webhook rejected: ' . $order->get_error_message() );
			return new WP_REST_Response( [ 'success' => false, 'message' => $order->get_error_message() ], $status );
		}

		OrderHelper::save_infinitepay_meta(
			$order,
			[
				'TRANSACTION_NSU' => isset( $payload['transaction_nsu'] ) ? $payload['transaction_nsu'] : '',
				'RECEIPT_URL'     => isset( $payload['receipt_url'] ) ? $payload['receipt_url'] : '',
				'CAPTURE_METHOD'  => isset( $payload['capture_method'] ) ? $payload['capture_method'] : '',
				'INSTALLMENTS'    => isset( $payload['installments'] ) ? (string) $payload['installments'] : '',
				'INVOICE_SLUG'    => isset( $payload['invoice_slug'] ) ? $payload['invoice_slug'] : '',
			]
		);

		$capture_method = isset( $payload['capture_method'] ) ? $payload['capture_method'] : 'checkout';
		OrderHelper::mark_as_processing( $order, sprintf(
			__( 'Pagamento confirmado via InfinitePay (%s).', 'infinitepay-woocommerce' ),
			$capture_method
		) );

		do_action( 'infinitepay_payment_confirmed', $order, $payload );

		$this->logger->info( 'Webhook processed: order #' . $order->get_id() );

		return new WP_REST_Response( [ 'success' => true, 'message' => null ], 200 );
	}
}
