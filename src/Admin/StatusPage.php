<?php

namespace InfinitePay\WooCommerce\Admin;

use Automattic\WooCommerce\Utilities\OrderUtil;

defined( 'ABSPATH' ) || exit;

class StatusPage {

	public function register(): void {
		add_action( 'woocommerce_system_status_report', [ $this, 'render' ] );
	}

	public function render(): void {
		$settings   = get_option( 'woocommerce_infinitepay_checkout_settings', [] );
		$handle     = isset( $settings['handle'] ) ? $settings['handle'] : '';
		$debug      = isset( $settings['debug'] ) && 'yes' === $settings['debug'];
		$hpos       = class_exists( OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled();
		$blocks     = class_exists( '\Automattic\WooCommerce\Blocks\Package' );
		$cron       = (bool) wp_next_scheduled( 'infinitepay_check_pending_payments' );
		$api_ok     = $this->test_api_connectivity();
		$webhook_url = rest_url( 'infinitepay/v1/webhook' );
		?>
		<table class="wc_status_table widefat" cellspacing="0">
			<thead>
				<tr>
					<th colspan="3"><h2><?php esc_html_e( 'InfinitePay', 'infinitepay-woocommerce' ); ?></h2></th>
				</tr>
			</thead>
			<tbody>
				<?php $this->row( __( 'Handle (InfiniteTag)', 'infinitepay-woocommerce' ), $handle ? '✅ ' . esc_html( substr( $handle, 0, 3 ) . '***' ) : '❌ ' . __( 'Não configurado', 'infinitepay-woocommerce' ) ); ?>
				<?php $this->row( __( 'Conectividade API', 'infinitepay-woocommerce' ), $api_ok ? '✅ OK' : '❌ Falha' ); ?>
				<?php $this->row( __( 'URL do Webhook', 'infinitepay-woocommerce' ), esc_url( $webhook_url ) ); ?>
				<?php $this->row( __( 'HPOS ativo', 'infinitepay-woocommerce' ), $hpos ? '✅ Sim' : '— Não' ); ?>
				<?php $this->row( __( 'Checkout Blocks', 'infinitepay-woocommerce' ), $blocks ? '✅ Disponível' : '— Não disponível' ); ?>
				<?php $this->row( __( 'Modo debug', 'infinitepay-woocommerce' ), $debug ? '⚠️ Ativo' : '✅ Inativo' ); ?>
				<?php $this->row( __( 'Versão PHP', 'infinitepay-woocommerce' ), version_compare( PHP_VERSION, '7.4', '>=' ) ? '✅ ' . PHP_VERSION : '❌ ' . PHP_VERSION ); ?>
				<?php $this->row( __( 'Cron agendado', 'infinitepay-woocommerce' ), $cron ? '✅ Sim' : '❌ Não' ); ?>
			</tbody>
		</table>
		<?php
	}

	private function row( string $label, string $value ): void {
		echo '<tr><td>' . esc_html( $label ) . '</td><td>' . wp_kses_post( $value ) . '</td></tr>';
	}

	private function test_api_connectivity(): bool {
		$response = wp_remote_get(
			'https://api.checkout.infinitepay.io',
			[ 'timeout' => 5, 'sslverify' => true ]
		);
		return ! is_wp_error( $response );
	}
}
