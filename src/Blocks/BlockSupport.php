<?php

namespace InfinitePay\WooCommerce\Blocks;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

defined( 'ABSPATH' ) || exit;

class BlockSupport extends AbstractPaymentMethodType {

	protected $name = 'infinitepay_checkout';

	public function initialize(): void {
		$this->settings = get_option( 'woocommerce_infinitepay_checkout_settings', [] );
	}

	public function is_active(): bool {
		return ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled']
			&& ! empty( $this->settings['handle'] );
	}

	public function get_payment_method_script_handles(): array {
		wp_register_script(
			'infinitepay-blocks',
			INFINITEPAY_WC_URL . 'assets/js/checkout-blocks.js',
			[ 'wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-html-entities' ],
			INFINITEPAY_WC_VERSION,
			true
		);

		return [ 'infinitepay-blocks' ];
	}

	public function get_payment_method_data(): array {
		return [
			'title'       => isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Pague com InfinitePay', 'infinitepay-woocommerce' ),
			'description' => isset( $this->settings['description'] ) ? $this->settings['description'] : '',
			'icon'        => INFINITEPAY_WC_URL . 'assets/images/infinitepay-icon.png',
			'supports'    => [ 'products' ],
			'ariaLabel'   => __( 'InfinitePay payment method', 'infinitepay-woocommerce' ),
		];
	}
}
