<?php

namespace InfinitePay\WooCommerce\Webhooks;

use InfinitePay\WooCommerce\Logger;
use InfinitePay\WooCommerce\Order\OrderHelper;
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
			$code   = $order->get_error_code();
			$status = 'infinitepay_already_paid' === $code ? 200 : 400;
			$this->logger->warning( 'Webhook rejected: ' . $order->get_error_message() );
			return new WP_REST_Response( [ 'success' => false, 'message' => $order->get_error_message() ], $status );
		}

		// Persiste os metadados imediatamente — garante transaction_nsu/slug para as
		// verificações posteriores (cron, botão admin, página de retorno).
		OrderHelper::save_infinitepay_meta(
			$order,
			[
				'TRANSACTION_NSU' => isset( $payload['transaction_nsu'] ) ? (string) $payload['transaction_nsu'] : '',
				'RECEIPT_URL'     => isset( $payload['receipt_url'] ) ? (string) $payload['receipt_url'] : '',
				'CAPTURE_METHOD'  => isset( $payload['capture_method'] ) ? (string) $payload['capture_method'] : '',
				'INSTALLMENTS'    => isset( $payload['installments'] ) ? (string) $payload['installments'] : '',
				'INVOICE_SLUG'    => isset( $payload['invoice_slug'] ) ? (string) $payload['invoice_slug'] : '',
			]
		);

		$paid_amount       = isset( $payload['paid_amount'] ) ? (int) $payload['paid_amount'] : 0;
		$order_total_cents = (int) round( $order->get_total() * 100 );

		if ( $paid_amount > 0 && $paid_amount >= ( $order_total_cents - 1 ) ) {
			$capture_method = isset( $payload['capture_method'] ) ? (string) $payload['capture_method'] : 'checkout';
			OrderHelper::mark_as_processing(
				$order,
				sprintf(
					/* translators: %s: capture method (pix, credit_card, etc.) */
					__( 'Pagamento confirmado via InfinitePay (%s).', 'infinitepay-woocommerce' ),
					$capture_method
				)
			);
			do_action( 'infinitepay_payment_confirmed', $order, $payload );
			$this->logger->info( 'Webhook processed: order #' . $order->get_id() );
		} else {
			// paid_amount ausente ou inconsistente — retorna 200 para evitar retry
			// da InfinitePay e agenda verificação por cron como rede de segurança.
			$this->logger->warning(
				sprintf(
					'Webhook: paid_amount inconsistente para pedido #%d (esperado %d centavos, recebido %d). Verificação por cron agendada.',
					$order->get_id(),
					$order_total_cents,
					$paid_amount
				)
			);
			wp_schedule_single_event( time() + 30, 'infinitepay_check_pending_payments' );
		}

		return new WP_REST_Response( [ 'success' => true, 'message' => null ], 200 );
	}
}
