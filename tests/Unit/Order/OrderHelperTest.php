<?php

namespace InfinitePay\WooCommerce\Tests\Unit\Order;

use InfinitePay\WooCommerce\Order\OrderHelper;
use InfinitePay\WooCommerce\Order\OrderMetaKeys;
use PHPUnit\Framework\TestCase;
use WC_Order;

class OrderHelperTest extends TestCase {

	public function test_save_infinitepay_meta_stores_values(): void {
		$order = new WC_Order( 1 );

		OrderHelper::save_infinitepay_meta( $order, [
			'CHECKOUT_URL' => 'https://checkout.infinitepay.io/pay/abc',
			'ORDER_NSU'    => 'WC-1-1700000000',
		] );

		$this->assertEquals(
			'https://checkout.infinitepay.io/pay/abc',
			$order->get_meta( OrderMetaKeys::CHECKOUT_URL )
		);
		$this->assertEquals(
			'WC-1-1700000000',
			$order->get_meta( OrderMetaKeys::ORDER_NSU )
		);
	}

	public function test_save_infinitepay_meta_ignores_empty_values(): void {
		$order = new WC_Order( 1 );
		$order->update_meta_data( OrderMetaKeys::CHECKOUT_URL, 'original' );

		OrderHelper::save_infinitepay_meta( $order, [ 'CHECKOUT_URL' => '' ] );

		$this->assertEquals( 'original', $order->get_meta( OrderMetaKeys::CHECKOUT_URL ) );
	}

	public function test_get_meta_returns_string(): void {
		$order = new WC_Order( 1 );
		$order->update_meta_data( OrderMetaKeys::ORDER_NSU, 'WC-1-123' );

		$result = OrderHelper::get_meta( $order, OrderMetaKeys::ORDER_NSU );
		$this->assertIsString( $result );
		$this->assertEquals( 'WC-1-123', $result );
	}

	public function test_mark_as_processing_changes_status(): void {
		$order = new WC_Order( 1 );
		OrderHelper::mark_as_processing( $order, 'Pagamento confirmado.' );
		$this->assertEquals( 'processing', $order->get_status() );
	}

	public function test_mark_as_cancelled_changes_status(): void {
		$order = new WC_Order( 1 );
		OrderHelper::mark_as_cancelled( $order, 'Timeout.' );
		$this->assertEquals( 'cancelled', $order->get_status() );
	}
}
