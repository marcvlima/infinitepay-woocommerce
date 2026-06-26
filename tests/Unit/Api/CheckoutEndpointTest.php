<?php

namespace InfinitePay\WooCommerce\Tests\Unit\Api;

use InfinitePay\WooCommerce\Api\CheckoutEndpoint;
use InfinitePay\WooCommerce\Api\Client;
use PHPUnit\Framework\TestCase;
use WP_Error;

class CheckoutEndpointTest extends TestCase {

	private function make_endpoint( $client_return ): CheckoutEndpoint {
		$client = $this->createMock( Client::class );
		$client->method( 'post' )->willReturn( $client_return );
		return new CheckoutEndpoint( $client );
	}

	public function test_returns_wp_error_when_handle_missing(): void {
		$endpoint = $this->make_endpoint( [] );
		$result   = $endpoint->create_link( [ 'items' => [ [] ], 'redirect_url' => 'https://example.com' ] );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertEquals( 'infinitepay_missing_handle', $result->get_error_code() );
	}

	public function test_returns_wp_error_when_items_missing(): void {
		$endpoint = $this->make_endpoint( [] );
		$result   = $endpoint->create_link( [ 'handle' => 'test', 'redirect_url' => 'https://example.com' ] );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertEquals( 'infinitepay_missing_items', $result->get_error_code() );
	}

	public function test_returns_url_on_success(): void {
		$endpoint = $this->make_endpoint( [ 'url' => 'https://checkout.infinitepay.io/pay/abc' ] );
		$result   = $endpoint->create_link( [
			'handle'       => 'lojamodelo',
			'items'        => [ [ 'quantity' => 1, 'price' => 9900, 'description' => 'Produto' ] ],
			'redirect_url' => 'https://loja.com/obrigado',
		] );
		$this->assertIsArray( $result );
		$this->assertEquals( 'https://checkout.infinitepay.io/pay/abc', $result['url'] );
	}

	public function test_returns_wp_error_when_api_returns_no_url(): void {
		$endpoint = $this->make_endpoint( [ 'status' => 'ok' ] );
		$result   = $endpoint->create_link( [
			'handle'       => 'lojamodelo',
			'items'        => [ [ 'quantity' => 1, 'price' => 9900, 'description' => 'Produto' ] ],
			'redirect_url' => 'https://loja.com/obrigado',
		] );
		$this->assertInstanceOf( WP_Error::class, $result );
	}
}
