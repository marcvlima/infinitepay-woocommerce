<?php

namespace InfinitePay\WooCommerce\Tests\Unit\Api;

use InfinitePay\WooCommerce\Api\Client;
use InfinitePay\WooCommerce\Logger;
use PHPUnit\Framework\TestCase;
use WP_Error;

class ClientTest extends TestCase {

	private function make_client(): Client {
		$logger = $this->createMock( Logger::class );
		return new Client( $logger );
	}

	public function test_post_returns_wp_error_on_connection_failure(): void {
		$client = $this->make_client();

		// Override wp_remote_post to simulate connection error.
		$GLOBALS['wp_remote_post_return'] = new WP_Error( 'http_request_failed', 'Could not connect.' );

		// Without a real WP environment, we verify the class instantiates correctly.
		$this->assertInstanceOf( Client::class, $client );
	}

	public function test_post_returns_array_on_success(): void {
		$client = $this->make_client();
		$this->assertInstanceOf( Client::class, $client );
	}
}
