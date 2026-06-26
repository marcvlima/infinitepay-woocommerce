<?php

namespace InfinitePay\WooCommerce\Checkout;

defined( 'ABSPATH' ) || exit;

class ModalHandler {

	private $enabled;

	public function __construct( bool $enabled = true ) {
		$this->enabled = $enabled;
	}

	public function register(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_checkout_scripts' ] );
	}

	public function enqueue_checkout_scripts(): void {
		if ( ! is_checkout() || ! $this->enabled ) {
			return;
		}

		wp_enqueue_script(
			'infinitepay-modal',
			INFINITEPAY_WC_URL . 'assets/js/modal-handler.js',
			[ 'jquery' ],
			INFINITEPAY_WC_VERSION,
			true
		);

		wp_localize_script(
			'infinitepay-modal',
			'InfinityPayModal',
			[
				'thankyouUrl' => wc_get_endpoint_url( 'order-received', '', wc_get_page_permalink( 'checkout' ) ),
				'nonce'       => wp_create_nonce( 'infinitepay_modal' ),
				'closeWarning' => __( 'Seu pagamento está sendo processado. Você receberá confirmação por e-mail.', 'infinitepay-woocommerce' ),
			]
		);
	}

	public function get_modal_enabled(): bool {
		return $this->enabled;
	}
}
