<?php

namespace InfinitePay\WooCommerce\Gateways;

use InfinitePay\WooCommerce\Api\Client;
use InfinitePay\WooCommerce\Logger;
use WC_Payment_Gateway;

defined( 'ABSPATH' ) || exit;

abstract class AbstractGateway extends WC_Payment_Gateway {

	protected $logger;
	protected $client;

	public function __construct() {
		$this->logger = new Logger();
		$this->client = new Client( $this->logger );

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
	}

	public function init_form_fields(): void {
		$this->form_fields = [
			'enabled'     => [
				'title'   => __( 'Enable/Disable', 'infinitepay-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable InfinitePay', 'infinitepay-woocommerce' ),
				'default' => 'no',
			],
			'title'       => [
				'title'       => __( 'Title', 'infinitepay-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Payment method title shown to the customer.', 'infinitepay-woocommerce' ),
				'default'     => __( 'Pague com InfinitePay', 'infinitepay-woocommerce' ),
				'desc_tip'    => true,
			],
			'description' => [
				'title'   => __( 'Description', 'infinitepay-woocommerce' ),
				'type'    => 'textarea',
				'default' => '',
			],
			'handle'      => [
				'title'       => __( 'InfiniteTag (handle)', 'infinitepay-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Your InfiniteTag without the $ sign.', 'infinitepay-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			],
			'store_name'  => [
				'title'       => __( 'Nome da loja no checkout', 'infinitepay-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Nome exibido na página de pagamento InfinitePay. Deixe vazio para usar o padrão da conta.', 'infinitepay-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			],
			'debug'       => [
				'title'   => __( 'Debug', 'infinitepay-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable debug logging', 'infinitepay-woocommerce' ),
				'default' => 'no',
			],
		];
	}

	public function is_available(): bool {
		if ( ! parent::is_available() ) {
			return false;
		}
		return ! empty( $this->get_handle() );
	}

	public function get_handle(): string {
		return (string) $this->get_option( 'handle' );
	}

	public function is_debug(): bool {
		return 'yes' === $this->get_option( 'debug' );
	}
}
