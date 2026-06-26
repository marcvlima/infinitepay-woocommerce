<?php

namespace InfinitePay\WooCommerce\Order;

use WC_Order;

defined( 'ABSPATH' ) || exit;

class OrderHelper {

	public static function find_order_by_nsu( string $nsu ): ?WC_Order {
		$orders = wc_get_orders(
			[
				'limit'      => 1,
				'meta_key'   => OrderMetaKeys::ORDER_NSU,
				'meta_value' => $nsu,
			]
		);

		return ! empty( $orders ) ? $orders[0] : null;
	}

	public static function save_infinitepay_meta( WC_Order $order, array $data ): void {
		$key_map = [
			'CHECKOUT_URL'       => OrderMetaKeys::CHECKOUT_URL,
			'ORDER_NSU'          => OrderMetaKeys::ORDER_NSU,
			'TRANSACTION_NSU'    => OrderMetaKeys::TRANSACTION_NSU,
			'RECEIPT_URL'        => OrderMetaKeys::RECEIPT_URL,
			'CAPTURE_METHOD'     => OrderMetaKeys::CAPTURE_METHOD,
			'INSTALLMENTS'       => OrderMetaKeys::INSTALLMENTS,
			'INVOICE_SLUG'       => OrderMetaKeys::INVOICE_SLUG,
			'PAYMENT_CHECKED_AT' => OrderMetaKeys::PAYMENT_CHECKED_AT,
		];

		foreach ( $data as $key => $value ) {
			if ( isset( $key_map[ $key ] ) && '' !== $value ) {
				$order->update_meta_data( $key_map[ $key ], $value );
			}
		}

		$order->save();
	}

	public static function get_meta( WC_Order $order, string $key ): string {
		return (string) $order->get_meta( $key, true );
	}

	public static function mark_as_processing( WC_Order $order, string $note = '' ): void {
		$order->update_meta_data( OrderMetaKeys::PAYMENT_CHECKED_AT, current_time( 'mysql' ) );
		$order->save();
		$order->payment_complete();
		if ( $note ) {
			$order->add_order_note( $note );
		}
	}

	public static function mark_as_cancelled( WC_Order $order, string $reason = '' ): void {
		$order->update_status( 'cancelled', $reason );
	}

	/**
	 * Returns pending InfinitePay orders older than $minutes with no payment confirmation.
	 *
	 * @return WC_Order[]
	 */
	public static function get_pending_orders_older_than( int $minutes ): array {
		return wc_get_orders(
			[
				'status'       => 'pending',
				'limit'        => 50,
				'date_created' => '<' . ( time() - $minutes * 60 ),
				'meta_query'   => [
					[
						'key'     => OrderMetaKeys::ORDER_NSU,
						'compare' => 'EXISTS',
					],
				],
			]
		);
	}
}
