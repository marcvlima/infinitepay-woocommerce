<?php

namespace InfinitePay\WooCommerce\Api;

use WP_Error;

defined( 'ABSPATH' ) || exit;

class PaymentCheckEndpoint {

	private $client;

	public function __construct( Client $client ) {
		$this->client = $client;
	}

	/**
	 * Checks payment status for an order.
	 *
	 * @return array|WP_Error Array with paid, amount, paid_amount, installments, capture_method.
	 */
	public function check( string $handle, string $order_nsu, string $transaction_nsu = '', string $slug = '' ) {
		$body = [
			'handle'    => $handle,
			'order_nsu' => $order_nsu,
		];

		if ( $transaction_nsu ) {
			$body['transaction_nsu'] = $transaction_nsu;
		}

		if ( $slug ) {
			$body['slug'] = $slug;
		}

		$response = $this->client->post( '/payment_check', $body );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return [
			'paid'           => ! empty( $response['paid'] ),
			'amount'         => isset( $response['amount'] ) ? (int) $response['amount'] : 0,
			'paid_amount'    => isset( $response['paid_amount'] ) ? (int) $response['paid_amount'] : 0,
			'installments'   => isset( $response['installments'] ) ? (int) $response['installments'] : 1,
			'capture_method' => isset( $response['capture_method'] ) ? (string) $response['capture_method'] : '',
		];
	}
}
