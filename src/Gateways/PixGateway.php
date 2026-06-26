<?php

namespace InfinitePay\WooCommerce\Gateways;

defined( 'ABSPATH' ) || exit;

class PixGateway extends CheckoutGateway {

	public function __construct() {
		$this->id                 = 'infinitepay_pix';
		$this->has_fields         = false;
		$this->method_title       = __( 'InfinitePay Pix', 'infinitepay-woocommerce' );
		$this->method_description = __( 'Receba pagamentos instantâneos via Pix pela InfinitePay.', 'infinitepay-woocommerce' );
		$this->supports           = [ 'products' ];

		// Skip CheckoutGateway::__construct, call AbstractGateway directly.
		AbstractGateway::__construct();

		$this->title       = $this->get_option( 'title', __( 'Pague com Pix', 'infinitepay-woocommerce' ) );
		$this->description = $this->get_option( 'description', __( 'Pagamento instantâneo via Pix. O QR Code será gerado após o redirecionamento.', 'infinitepay-woocommerce' ) );
	}

	public function init_form_fields(): void {
		parent::init_form_fields();
		$this->form_fields['title']['default']       = __( 'Pague com Pix', 'infinitepay-woocommerce' );
		$this->form_fields['description']['default'] = __( 'Pagamento instantâneo via Pix. O QR Code será gerado após o redirecionamento.', 'infinitepay-woocommerce' );
	}

	public function get_icon(): string {
		$icon = '<img src="' . esc_url( INFINITEPAY_WC_URL . 'assets/images/pix-logo.svg' ) . '" alt="Pix" style="max-height:24px;">';
		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
	}
}
