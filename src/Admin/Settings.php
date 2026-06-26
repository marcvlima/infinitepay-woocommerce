<?php

namespace InfinitePay\WooCommerce\Admin;

defined( 'ABSPATH' ) || exit;

class Settings {

	public static function get_redirect_settings(): array {
		$saved = get_option( 'woocommerce_infinitepay_checkout_settings', [] );

		return [
			'redirect_screen_enabled' => isset( $saved['redirect_screen_enabled'] ) ? $saved['redirect_screen_enabled'] : 'yes',
			'redirect_logo'           => isset( $saved['redirect_logo'] ) ? esc_url_raw( $saved['redirect_logo'] ) : '',
			'redirect_message'        => isset( $saved['redirect_message'] ) ? sanitize_text_field( $saved['redirect_message'] ) : __( 'Aguarde, você está sendo redirecionado para o pagamento...', 'infinitepay-woocommerce' ),
			'redirect_fallback_text'  => isset( $saved['redirect_fallback_text'] ) ? sanitize_text_field( $saved['redirect_fallback_text'] ) : __( 'Clique aqui se não for redirecionado automaticamente', 'infinitepay-woocommerce' ),
			'redirect_security_text'  => isset( $saved['redirect_security_text'] ) ? sanitize_text_field( $saved['redirect_security_text'] ) : __( 'Ambiente seguro InfinitePay', 'infinitepay-woocommerce' ),
			'redirect_bg_color'       => isset( $saved['redirect_bg_color'] ) ? sanitize_hex_color( $saved['redirect_bg_color'] ) : '#ffffff',
			'redirect_text_color'     => isset( $saved['redirect_text_color'] ) ? sanitize_hex_color( $saved['redirect_text_color'] ) : '#333333',
			'redirect_accent_color'   => isset( $saved['redirect_accent_color'] ) ? sanitize_hex_color( $saved['redirect_accent_color'] ) : '#1a1a2e',
			'redirect_delay_seconds'  => isset( $saved['redirect_delay_seconds'] ) ? absint( $saved['redirect_delay_seconds'] ) : 3,
		];
	}

	public static function get_redirect_form_fields(): array {
		return [
			'redirect_screen_title'   => [
				'title' => __( 'Tela de Transição', 'infinitepay-woocommerce' ),
				'type'  => 'title',
			],
			'redirect_screen_enabled' => [
				'title'   => __( 'Ativar tela de transição', 'infinitepay-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Exibir tela intermediária antes do redirecionamento', 'infinitepay-woocommerce' ),
				'default' => 'yes',
			],
			'redirect_logo'           => [
				'title'       => __( 'Logo', 'infinitepay-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'URL da imagem do logo (deixe em branco para usar o logo do site).', 'infinitepay-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			],
			'redirect_message'        => [
				'title'   => __( 'Mensagem', 'infinitepay-woocommerce' ),
				'type'    => 'textarea',
				'default' => __( 'Aguarde, você está sendo redirecionado para o pagamento...', 'infinitepay-woocommerce' ),
			],
			'redirect_fallback_text'  => [
				'title'   => __( 'Texto do link de fallback', 'infinitepay-woocommerce' ),
				'type'    => 'text',
				'default' => __( 'Clique aqui se não for redirecionado automaticamente', 'infinitepay-woocommerce' ),
			],
			'redirect_security_text'  => [
				'title'   => __( 'Texto de segurança', 'infinitepay-woocommerce' ),
				'type'    => 'text',
				'default' => __( 'Ambiente seguro InfinitePay', 'infinitepay-woocommerce' ),
			],
			'redirect_bg_color'       => [
				'title'   => __( 'Cor de fundo', 'infinitepay-woocommerce' ),
				'type'    => 'color',
				'default' => '#ffffff',
			],
			'redirect_text_color'     => [
				'title'   => __( 'Cor do texto', 'infinitepay-woocommerce' ),
				'type'    => 'color',
				'default' => '#333333',
			],
			'redirect_accent_color'   => [
				'title'   => __( 'Cor de destaque', 'infinitepay-woocommerce' ),
				'type'    => 'color',
				'default' => '#1a1a2e',
			],
			'redirect_delay_seconds'  => [
				'title'             => __( 'Delay do redirecionamento (segundos)', 'infinitepay-woocommerce' ),
				'type'              => 'number',
				'default'           => 3,
				'custom_attributes' => [ 'min' => 1, 'max' => 10 ],
			],
		];
	}
}
