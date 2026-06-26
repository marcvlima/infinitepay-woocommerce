<?php

namespace InfinitePay\WooCommerce;

defined( 'ABSPATH' ) || exit;

class Logger {

	private $logger;
	private $source = 'infinitepay-woocommerce';

	public function __construct() {
		$this->logger = wc_get_logger();
	}

	public function debug( string $message, array $context = [] ): void {
		if ( ! $this->is_debug_enabled() ) {
			return;
		}
		$this->logger->debug( $this->mask_handle( $message ), $this->context( $context ) );
	}

	public function info( string $message, array $context = [] ): void {
		$this->logger->info( $this->mask_handle( $message ), $this->context( $context ) );
	}

	public function warning( string $message, array $context = [] ): void {
		$this->logger->warning( $this->mask_handle( $message ), $this->context( $context ) );
	}

	public function error( string $message, array $context = [] ): void {
		$this->logger->error( $this->mask_handle( $message ), $this->context( $context ) );
	}

	private function context( array $context ): array {
		return array_merge( [ 'source' => $this->source ], $context );
	}

	/**
	 * Masks InfiniteTag handle — shows first 3 chars followed by ***.
	 * Prevents credential leakage in log files.
	 */
	private function mask_handle( string $message ): string {
		return preg_replace_callback(
			'/\$[A-Za-z0-9]{4,}/',
			function ( $matches ) {
				$handle = $matches[0];
				return substr( $handle, 0, 4 ) . '***';
			},
			$message
		);
	}

	private function is_debug_enabled(): bool {
		$settings = get_option( 'woocommerce_infinitepay_checkout_settings', [] );
		return isset( $settings['debug'] ) && 'yes' === $settings['debug'];
	}
}
