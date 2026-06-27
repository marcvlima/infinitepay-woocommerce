<?php

namespace InfinitePay\WooCommerce\Webhooks;

use InfinitePay\WooCommerce\Logger;
use InfinitePay\WooCommerce\Order\OrderHelper;
use WC_Order;
use WP_Error;

defined( 'ABSPATH' ) || exit;

class WebhookValidator {

	private $logger;

	public function __construct( Logger $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Valida estrutura do payload, existência do pedido, método e idempotência.
	 * Não faz back-call ao /payment_check — a verificação de valor é feita pelo
	 * WebhookHandler a partir do paid_amount do próprio payload.
	 *
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

		if ( in_array( $order->get_status(), [ 'processing', 'completed' ], true ) ) {
			return new WP_Error( 'infinitepay_already_paid', 'Order already processed.' );
		}

		return $order;
	}
}
