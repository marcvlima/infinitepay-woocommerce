<?php

namespace InfinitePay\WooCommerce\PaymentRecovery;

use InfinitePay\WooCommerce\Api\PaymentCheckEndpoint;
use InfinitePay\WooCommerce\Logger;
use InfinitePay\WooCommerce\Order\OrderHelper;
use InfinitePay\WooCommerce\Order\OrderMetaKeys;

defined( 'ABSPATH' ) || exit;

class ReturnHandler {

	private $payment_check;
	private $logger;
	private $handle;

	public function __construct( PaymentCheckEndpoint $payment_check, Logger $logger, string $handle ) {
		$this->payment_check = $payment_check;
		$this->logger        = $logger;
		$this->handle        = $handle;
	}

	public function register(): void {
		add_action( 'woocommerce_thankyou', [ $this, 'handle_return' ] );
	}

	public function handle_return( int $order_id ): void {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$method = $order->get_payment_method();
		if ( ! in_array( $method, [ 'infinitepay_checkout', 'infinitepay_pix' ], true ) ) {
			return;
		}

		if ( in_array( $order->get_status(), [ 'processing', 'completed' ], true ) ) {
			$this->maybe_show_receipt( $order );
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$transaction_nsu = isset( $_GET['transaction_nsu'] ) ? sanitize_text_field( wp_unslash( $_GET['transaction_nsu'] ) ) : '';
		$slug            = isset( $_GET['slug'] ) ? sanitize_text_field( wp_unslash( $_GET['slug'] ) ) : '';
		$capture_method  = isset( $_GET['capture_method'] ) ? sanitize_text_field( wp_unslash( $_GET['capture_method'] ) ) : '';
		// phpcs:enable

		if ( $transaction_nsu || $slug ) {
			OrderHelper::save_infinitepay_meta(
				$order,
				[
					'TRANSACTION_NSU' => $transaction_nsu,
					'INVOICE_SLUG'    => $slug,
					'CAPTURE_METHOD'  => $capture_method,
				]
			);
		}

		$order_nsu = OrderHelper::get_meta( $order, OrderMetaKeys::ORDER_NSU );
		if ( ! $order_nsu ) {
			return;
		}

		$check = $this->payment_check->check( $this->handle, $order_nsu, $transaction_nsu, $slug );

		if ( is_wp_error( $check ) ) {
			$this->logger->warning( 'ReturnHandler: check failed for order #' . $order_id . ': ' . $check->get_error_message() );
			return;
		}

		if ( ! empty( $check['paid'] ) ) {
			OrderHelper::mark_as_processing( $order, __( 'Pagamento confirmado na página de retorno.', 'infinitepay-woocommerce' ) );
		}

		$this->maybe_show_receipt( $order );
	}

	private function maybe_show_receipt( $order ): void {
		$receipt_url = OrderHelper::get_meta( $order, OrderMetaKeys::RECEIPT_URL );
		if ( $receipt_url ) {
			echo '<p class="infinitepay-receipt-link"><a href="' . esc_url( $receipt_url ) . '" target="_blank" rel="noopener">'
				. esc_html__( 'Ver comprovante de pagamento', 'infinitepay-woocommerce' )
				. '</a></p>';
		}
	}
}
