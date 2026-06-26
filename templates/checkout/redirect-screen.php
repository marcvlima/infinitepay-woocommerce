<?php
/**
 * InfinitePay redirect screen template.
 *
 * @var string $logo_url
 * @var string $logo_alt
 * @var string $store_name
 * @var string $message
 * @var string $security_text
 * @var string $fallback_text
 * @var string $checkout_url
 * @var string $bg_color
 * @var string $text_color
 * @var string $accent_color
 * @var int    $delay_seconds
 */

defined( 'ABSPATH' ) || exit;
?>
<style>
	:root {
		--infinitepay-bg:     <?php echo esc_attr( $bg_color ); ?>;
		--infinitepay-text:   <?php echo esc_attr( $text_color ); ?>;
		--infinitepay-accent: <?php echo esc_attr( $accent_color ); ?>;
	}
</style>

<div class="infinitepay-redirect-overlay" role="status" aria-live="polite">
	<div class="infinitepay-redirect-box">

		<?php if ( $logo_url ) : ?>
			<div class="infinitepay-redirect-logo">
				<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $logo_alt ); ?>">
			</div>
		<?php else : ?>
			<div class="infinitepay-redirect-logo infinitepay-redirect-logo--text">
				<?php echo esc_html( $store_name ); ?>
			</div>
		<?php endif; ?>

		<div class="infinitepay-spinner" aria-hidden="true"></div>

		<div class="infinitepay-progress-bar">
			<div class="infinitepay-progress-fill" style="animation-duration: <?php echo esc_attr( $delay_seconds ); ?>s"></div>
		</div>

		<p class="infinitepay-redirect-message">
			<?php echo esc_html( $message ); ?>
		</p>

		<p class="infinitepay-redirect-security">
			<span class="infinitepay-lock-icon" aria-hidden="true">&#128274;</span>
			<?php echo esc_html( $security_text ); ?>
		</p>

		<p class="infinitepay-redirect-fallback">
			<a href="<?php echo esc_url( $checkout_url ); ?>">
				<?php echo esc_html( $fallback_text ); ?>
			</a>
		</p>

	</div>
</div>
