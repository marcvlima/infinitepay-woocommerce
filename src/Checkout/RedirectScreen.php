<?php

namespace InfinitePay\WooCommerce\Checkout;

use InfinitePay\WooCommerce\Admin\Settings;

defined( 'ABSPATH' ) || exit;

class RedirectScreen {

	public function render( string $checkout_url ): void {
		$settings = Settings::get_redirect_settings();

		$logo_url = '';
		$logo_alt = get_bloginfo( 'name' );

		if ( ! empty( $settings['redirect_logo'] ) ) {
			$logo_url = $settings['redirect_logo'];
		} elseif ( has_custom_logo() ) {
			$logo_id  = get_theme_mod( 'custom_logo' );
			$logo_url = wp_get_attachment_image_url( $logo_id, 'medium' );
		}

		wp_enqueue_style(
			'infinitepay-redirect',
			INFINITEPAY_WC_URL . 'assets/css/redirect-screen.css',
			[],
			INFINITEPAY_WC_VERSION
		);

		wp_enqueue_script(
			'infinitepay-redirect',
			INFINITEPAY_WC_URL . 'assets/js/redirect-screen.js',
			[],
			INFINITEPAY_WC_VERSION,
			true
		);

		wp_localize_script(
			'infinitepay-redirect',
			'InfinityPayRedirect',
			[
				'url'   => esc_url( $checkout_url ),
				'delay' => (int) $settings['redirect_delay_seconds'],
			]
		);

		wc_get_template(
			'checkout/redirect-screen.php',
			[
				'logo_url'      => $logo_url,
				'logo_alt'      => $logo_alt,
				'store_name'    => get_bloginfo( 'name' ),
				'message'       => $settings['redirect_message'],
				'security_text' => $settings['redirect_security_text'],
				'fallback_text' => $settings['redirect_fallback_text'],
				'checkout_url'  => $checkout_url,
				'bg_color'      => $settings['redirect_bg_color'],
				'text_color'    => $settings['redirect_text_color'],
				'accent_color'  => $settings['redirect_accent_color'],
				'delay_seconds' => $settings['redirect_delay_seconds'],
			],
			'',
			INFINITEPAY_WC_PATH . 'templates/'
		);
	}
}
