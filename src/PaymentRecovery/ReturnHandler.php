<?php

namespace InfinitePay\WooCommerce\PaymentRecovery;

use InfinitePay\WooCommerce\Api\PaymentCheckEndpoint;
use InfinitePay\WooCommerce\Logger;
use InfinitePay\WooCommerce\Order\OrderHelper;
use InfinitePay\WooCommerce\Order\OrderMetaKeys;
use WC_Order;

defined( 'ABSPATH' ) || exit;

class ReturnHandler {

	private $payment_check;
	private $logger;
	private $handle;

	public function __construct( PaymentCheckEndpoint $payment_check, Logger $logger, string $handle ) {
		$this->payment_check = $payment_check;
		$this->logger        = $logger;
		$this->handle        = $handle;
	}

	public function register(): void {
		add_action( 'woocommerce_thankyou', [ $this, 'handle_return' ] );
	}

	public function handle_return( int $order_id ): void {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$method = $order->get_payment_method();
		if ( ! in_array( $method, [ 'infinitepay_checkout', 'infinitepay_pix' ], true ) ) {
			return;
		}

		// Se ainda não confirmado, tenta confirmar com os parâmetros do redirect.
		if ( ! in_array( $order->get_status(), [ 'processing', 'completed' ], true ) ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$transaction_nsu = isset( $_GET['transaction_nsu'] ) ? sanitize_text_field( wp_unslash( $_GET['transaction_nsu'] ) ) : '';
			$slug            = isset( $_GET['slug'] ) ? sanitize_text_field( wp_unslash( $_GET['slug'] ) ) : '';
			$capture_method  = isset( $_GET['capture_method'] ) ? sanitize_text_field( wp_unslash( $_GET['capture_method'] ) ) : '';
			// phpcs:enable

			if ( $transaction_nsu || $slug ) {
				OrderHelper::save_infinitepay_meta(
					$order,
					[
						'TRANSACTION_NSU' => $transaction_nsu,
						'INVOICE_SLUG'    => $slug,
						'CAPTURE_METHOD'  => $capture_method,
					]
				);
			}

			$order_nsu = OrderHelper::get_meta( $order, OrderMetaKeys::ORDER_NSU );
			if ( $order_nsu ) {
				$check = $this->payment_check->check( $this->handle, $order_nsu, $transaction_nsu, $slug );

				if ( is_wp_error( $check ) ) {
					$this->logger->warning( 'ReturnHandler: check failed for order #' . $order_id . ': ' . $check->get_error_message() );
				} elseif ( ! empty( $check['paid'] ) ) {
					if ( ! empty( $check['capture_method'] ) ) {
						OrderHelper::save_infinitepay_meta( $order, [ 'CAPTURE_METHOD' => (string) $check['capture_method'] ] );
					}
					OrderHelper::mark_as_processing( $order, __( 'Pagamento confirmado na página de retorno.', 'infinitepay-woocommerce' ) );
					$order = wc_get_order( $order_id );
				}
			}
		}

		$this->render_status_banner( $order );
	}

	private function render_status_banner( WC_Order $order ): void {
		$is_paid     = in_array( $order->get_status(), [ 'processing', 'completed' ], true );
		$receipt_url = OrderHelper::get_meta( $order, OrderMetaKeys::RECEIPT_URL );

		$gold  = '#C8A153';
		$dark  = '#161616';
		$cream = '#F0E6D0';

		if ( $is_paid ) {
			$accent = $gold;
			$icon   = '✓';
			$title  = __( 'Pagamento confirmado', 'infinitepay-woocommerce' );
			$text   = __( 'Recebemos a confirmação do seu pagamento. Você receberá um e-mail com os detalhes do pedido.', 'infinitepay-woocommerce' );
		} else {
			$accent = '#C9A227';
			$icon   = '⏳';
			$title  = __( 'Aguardando confirmação do pagamento', 'infinitepay-woocommerce' );
			$text   = __( 'Assim que o pagamento for confirmado, seu pedido será processado e você receberá um e-mail. Para Pix, isso pode levar alguns instantes.', 'infinitepay-woocommerce' );
		}
		?>
		<div class="am-ip-status" style="display:flex;gap:16px;align-items:flex-start;margin:24px 0;padding:20px 24px;border-radius:10px;border:1px solid <?php echo esc_attr( $accent ); ?>;border-left:5px solid <?php echo esc_attr( $accent ); ?>;background:<?php echo esc_attr( $dark ); ?>;color:<?php echo esc_attr( $cream ); ?>;">
			<span aria-hidden="true" style="font-size:28px;line-height:1;color:<?php echo esc_attr( $accent ); ?>;"><?php echo esc_html( $icon ); ?></span>
			<div style="flex:1;">
				<strong style="display:block;font-size:18px;margin-bottom:6px;color:<?php echo esc_attr( $accent ); ?>;"><?php echo esc_html( $title ); ?></strong>
				<p style="margin:0 0 12px;line-height:1.5;color:<?php echo esc_attr( $cream ); ?>;"><?php echo esc_html( $text ); ?></p>
				<?php if ( $receipt_url ) : ?>
					<a href="<?php echo esc_url( $receipt_url ); ?>" target="_blank" rel="noopener"
						style="display:inline-block;padding:10px 20px;border-radius:6px;background:<?php echo esc_attr( $gold ); ?>;color:<?php echo esc_attr( $dark ); ?>;font-weight:600;text-decoration:none;">
						<?php esc_html_e( 'Ver comprovante de pagamento', 'infinitepay-woocommerce' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
