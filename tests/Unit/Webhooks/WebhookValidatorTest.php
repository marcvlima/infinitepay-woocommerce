<?php

namespace InfinitePay\WooCommerce\Tests\Unit\Webhooks;

use InfinitePay\WooCommerce\Api\PaymentCheckEndpoint;
use InfinitePay\WooCommerce\Logger;
use InfinitePay\WooCommerce\Webhooks\WebhookValidator;
use PHPUnit\Framework\TestCase;
use WC_Order;
use WP_Error;

class WebhookValidatorTest extends TestCase {

	private function make_validator( array $check_return, ?WC_Order $found_order = null ): WebhookValidator {
		$payment_check = $this->createMock( PaymentCheckEndpoint::class );
		$payment_check->method( 'check' )->willReturn( $check_return );

		$logger    = $this->createMock( Logger::class );
		$validator = $this->getMockBuilder( WebhookValidator::class )
			->setConstructorArgs( [ $payment_check, $logger, 'testhandle' ] )
			->onlyMethods( [] )
			->getMock();

		// We'll test validate() logic via integration-style unit test.
		return new WebhookValidator( $payment_check, $logger, 'testhandle' );
	}

	public function test_returns_error_when_order_nsu_missing(): void {
		$validator = $this->make_validator( [ 'paid' => true, 'paid_amount' => 9900 ] );
		$result    = $validator->validate( [] );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertEquals( 'infinitepay_missing_nsu', $result->get_error_code() );
	}

	public function test_returns_error_when_order_not_found(): void {
		$validator = $this->make_validator( [ 'paid' => true, 'paid_amount' => 9900 ] );
		// Without WC environment, find_order_by_nsu returns null.
		$result = $validator->validate( [ 'order_nsu' => 'WC-999-000' ] );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertEquals( 'infinitepay_order_not_found', $result->get_error_code() );
	}
}
