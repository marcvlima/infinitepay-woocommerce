<?php

namespace InfinitePay\WooCommerce\Api;

use WP_Error;

defined( 'ABSPATH' ) || exit;

class CheckoutEndpoint {

	private $client;

	public function __construct( Client $client ) {
		$this->client = $client;
	}

	/**
	 * Creates a payment link via InfinitePay /links endpoint.
	 *
	 * @return array|WP_Error Array with 'url' key on success.
	 */
	public function create_link( array $payload ) {
		if ( empty( $payload['handle'] ) ) {
			return new WP_Error( 'infinitepay_missing_handle', __( 'InfiniteTag handle is required.', 'infinitepay-woocommerce' ) );
		}

		if ( empty( $payload['items'] ) || ! is_array( $payload['items'] ) ) {
			return new WP_Error( 'infinitepay_missing_items', __( 'Order items are required.', 'infinitepay-woocommerce' ) );
		}

		if ( empty( $payload['redirect_url'] ) ) {
			return new WP_Error( 'infinitepay_missing_redirect', __( 'Redirect URL is required.', 'infinitepay-woocommerce' ) );
		}

		$response = $this->client->post( '/links', $payload );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( empty( $response['url'] ) ) {
			return new WP_Error(
				'infinitepay_api_error',
				__( 'InfinitePay did not return a payment URL.', 'infinitepay-woocommerce' )
			);
		}

		return [ 'url' => $response['url'] ];
	}
}
