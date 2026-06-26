<?php

namespace InfinitePay\WooCommerce\PaymentRecovery;

use InfinitePay\WooCommerce\Api\PaymentCheckEndpoint;
use InfinitePay\WooCommerce\Logger;
use InfinitePay\WooCommerce\Order\OrderHelper;
use InfinitePay\WooCommerce\Order\OrderMetaKeys;

defined( 'ABSPATH' ) || exit;

class CronChecker {

	private $payment_check;
	private $logger;
	private $handle;

	public function __construct( PaymentCheckEndpoint $payment_check, Logger $logger, string $handle ) {
		$this->payment_check = $payment_check;
		$this->logger        = $logger;
		$this->handle        = $handle;
	}

	public function register(): void {
		add_filter( 'cron_schedules', [ $this, 'add_fifteen_minutes' ] );
		add_action( 'infinitepay_check_pending_payments', [ $this, 'check_pending_orders' ] );

		if ( ! wp_next_scheduled( 'infinitepay_check_pending_payments' ) ) {
			wp_schedule_event( time(), 'fifteen_minutes', 'infinitepay_check_pending_payments' );
		}
	}

	public function add_fifteen_minutes( array $schedules ): array {
		$schedules['fifteen_minutes'] = [
			'interval' => 900,
			'display'  => __( 'Every 15 minutes', 'infinitepay-woocommerce' ),
		];
		return $schedules;
	}

	public function check_pending_orders(): void {
		$orders = OrderHelper::get_pending_orders_older_than( 15 );

		foreach ( $orders as $order ) {
			$order_nsu = OrderHelper::get_meta( $order, OrderMetaKeys::ORDER_NSU );

			if ( ! $order_nsu ) {
				continue;
			}

			$check = $this->payment_check->check( $this->handle, $order_nsu );

			if ( is_wp_error( $check ) ) {
				$this->logger->warning( 'CronChecker: error checking order #' . $order->get_id() . ': ' . $check->get_error_message() );
				continue;
			}

			if ( ! empty( $check['paid'] ) ) {
				OrderHelper::mark_as_processing( $order, __( 'Pagamento confirmado via verificação automática InfinitePay.', 'infinitepay-woocommerce' ) );
				$this->logger->info( 'CronChecker: order #' . $order->get_id() . ' marked as processing.' );
				continue;
			}

			// Cancel orders pending for more than 24 hours.
			$created = $order->get_date_created();
			if ( $created && ( time() - $created->getTimestamp() ) > DAY_IN_SECONDS ) {
				OrderHelper::mark_as_cancelled( $order, __( 'Pedido cancelado automaticamente por falta de pagamento após 24h.', 'infinitepay-woocommerce' ) );
				$this->logger->info( 'CronChecker: order #' . $order->get_id() . ' cancelled after 24h.' );
			}
		}
	}

	public function deactivate(): void {
		wp_clear_scheduled_hook( 'infinitepay_check_pending_payments' );
	}
}
