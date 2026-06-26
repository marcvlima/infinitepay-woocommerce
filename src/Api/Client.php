<?php

namespace InfinitePay\WooCommerce\Api;

use InfinitePay\WooCommerce\Logger;
use WP_Error;

defined( 'ABSPATH' ) || exit;

class Client {

	const BASE_URL = 'https://api.checkout.infinitepay.io';
	const TIMEOUT  = 30;

	private $logger;

	public function __construct( Logger $logger ) {
		$this->logger = $logger;
	}

	/**
	 * @return array|WP_Error
	 */
	public function post( string $endpoint, array $body ) {
		$url = self::BASE_URL . $endpoint;

		$this->logger->debug( 'API request: POST ' . $endpoint );

		$response = wp_remote_post(
			$url,
			[
				'timeout' => self::TIMEOUT,
				'headers' => [
					'Content-Type' => 'application/json',
					'Accept'       => 'application/json',
				],
				'body'    => wp_json_encode( $body ),
			]
		);

		return $this->handle_response( $response, $endpoint );
	}

	/**
	 * @return array|WP_Error
	 */
	public function get( string $endpoint ) {
		$url = self::BASE_URL . $endpoint;

		$this->logger->debug( 'API request: GET ' . $endpoint );

		$response = wp_remote_get(
			$url,
			[
				'timeout' => self::TIMEOUT,
				'headers' => [
					'Accept' => 'application/json',
				],
			]
		);

		return $this->handle_response( $response, $endpoint );
	}

	/**
	 * @param array|WP_Error $response
	 * @return array|WP_Error
	 */
	private function handle_response( $response, string $endpoint ) {
		if ( is_wp_error( $response ) ) {
			$this->logger->error( 'API connection error on ' . $endpoint . ': ' . $response->get_error_message() );
			return new WP_Error(
				'infinitepay_connection_error',
				$response->get_error_message()
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		$this->logger->debug( 'API response ' . $endpoint . ': HTTP ' . $code );

		if ( $code >= 400 ) {
			$message = isset( $data['message'] ) ? $data['message'] : 'HTTP ' . $code;
			$this->logger->error( 'API error on ' . $endpoint . ': ' . $message );
			return new WP_Error( 'infinitepay_api_error', $message, [ 'status' => $code ] );
		}

		return is_array( $data ) ? $data : [];
	}
}
