<?php

namespace InfinitePay\WooCommerce\Gateways;

use InfinitePay\WooCommerce\Api\CheckoutEndpoint;
use InfinitePay\WooCommerce\Order\OrderHelper;
use InfinitePay\WooCommerce\Order\OrderMetaKeys;

defined( 'ABSPATH' ) || exit;

class CheckoutGateway extends AbstractGateway {

	public function __construct() {
		$this->id                 = 'infinitepay_checkout';
		$this->has_fields         = false;
		$this->method_title       = __( 'InfinitePay Checkout', 'infinitepay-woocommerce' );
		$this->method_description = __( 'Receba pagamentos via InfinitePay Checkout Integrado.', 'infinitepay-woocommerce' );
		$this->icon               = INFINITEPAY_WC_URL . 'assets/images/infinitepay-icon.svg';
		$this->supports           = [ 'products' ];

		parent::__construct();
	}

	public function get_icon(): string {
		$icon = '<img src="' . esc_url( INFINITEPAY_WC_URL . 'assets/images/infinitepay-icon.png' ) . '" alt="InfinitePay" style="max-height:24px;">';
		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
	}

	public function process_payment( $order_id ): array {
		$order    = wc_get_order( $order_id );
		$handle   = $this->get_handle();
		$endpoint = new CheckoutEndpoint( $this->client );

		$items = [];
		foreach ( $order->get_items() as $item ) {
			$product   = $item->get_product();
			$image_url = '';
			if ( $product ) {
				$img_id    = $product->get_image_id();
				$image_url = $img_id ? wp_get_attachment_image_url( $img_id, 'woocommerce_thumbnail' ) : '';
			}
			$item_data = [
				'quantity'    => $item->get_quantity(),
				'price'       => (int) round( ( $item->get_total() / $item->get_quantity() ) * 100 ),
				'description' => $item->get_name(),
			];
			if ( $image_url ) {
				$item_data['image_url'] = $image_url;
			}
			$items[] = $item_data;
		}

		// Extract street number from address_1 (e.g. "Rua X, 123" or custom field billing_number).
		$address1 = $order->get_billing_address_1();
		$number   = (string) $order->get_meta( '_billing_number' );
		if ( ! $number ) {
			preg_match( '/,\s*(\d+[A-Za-z]?)/', $address1, $m );
			$number = $m[1] ?? ( $order->get_billing_address_2() ?: '0' );
		}

		$order_nsu = 'WC-' . $order_id . '-' . time();

		$payload = [
			'handle'       => $handle,
			'order_nsu'    => $order_nsu,
			'items'        => $items,
			'redirect_url' => $order->get_checkout_order_received_url(),
			'webhook_url'  => rest_url( 'infinitepay/v1/webhook' ),
			'customer'     => [
				'name'         => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
				'email'        => $order->get_billing_email(),
				'phone_number' => preg_replace( '/\D/', '', $order->get_billing_phone() ),
			],
			'address'      => [
				'cep'        => preg_replace( '/\D/', '', $order->get_billing_postcode() ),
				'number'     => $number,
				'complement' => $order->get_billing_address_2(),
			],
		];

		$store_name = trim( (string) $this->get_option( 'store_name' ) );
		if ( $store_name ) {
			$payload['merchant'] = [ 'name' => $store_name ];
		}

		$result = $endpoint->create_link( $payload );

		if ( is_wp_error( $result ) ) {
			$this->logger->error( 'process_payment failed: ' . $result->get_error_message() );
			wc_add_notice( __( 'Erro ao processar pagamento InfinitePay. Tente novamente.', 'infinitepay-woocommerce' ), 'error' );
			return [ 'result' => 'failure' ];
		}

		$checkout_url = $result['url'];

		OrderHelper::save_infinitepay_meta(
			$order,
			[
				'CHECKOUT_URL' => $checkout_url,
				'ORDER_NSU'    => $order_nsu,
			]
		);

		$order->set_status( 'pending', __( 'Aguardando pagamento InfinitePay.', 'infinitepay-woocommerce' ) );
		$order->save();

		$settings = get_option( 'woocommerce_infinitepay_checkout_settings', [] );
		$use_modal = isset( $settings['use_modal'] ) && 'yes' === $settings['use_modal'];

		if ( $use_modal ) {
			WC()->session->set( 'infinitepay_checkout_url', $checkout_url );
			return [
				'result'   => 'success',
				'redirect' => $order->get_checkout_order_received_url(),
			];
		}

		return [
			'result'   => 'success',
			'redirect' => $checkout_url,
		];
	}
}
