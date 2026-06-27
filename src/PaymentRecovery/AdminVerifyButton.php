<?php

namespace InfinitePay\WooCommerce\PaymentRecovery;

use InfinitePay\WooCommerce\Api\PaymentCheckEndpoint;
use InfinitePay\WooCommerce\Logger;
use InfinitePay\WooCommerce\Order\OrderHelper;
use InfinitePay\WooCommerce\Order\OrderMetaKeys;
use WC_Order;

defined( 'ABSPATH' ) || exit;

class AdminVerifyButton {

	private $payment_check;
	private $logger;
	private $handle;

	public function __construct( PaymentCheckEndpoint $payment_check, Logger $logger, string $handle ) {
		$this->payment_check = $payment_check;
		$this->logger        = $logger;
		$this->handle        = $handle;
	}

	public function register(): void {
		add_action( 'woocommerce_admin_order_data_after_billing_address', [ $this, 'render_button' ] );
		add_action( 'wp_ajax_infinitepay_verify_payment', [ $this, 'ajax_verify' ] );
	}

	public function render_button( WC_Order $order ): void {
		$method = $order->get_payment_method();
		if ( ! in_array( $method, [ 'infinitepay_checkout', 'infinitepay_pix' ], true ) ) {
			return;
		}
		?>
		<div class="infinitepay-admin-verify" style="margin-top:12px;">
			<button type="button"
				class="button infinitepay-verify-btn"
				data-order-id="<?php echo esc_attr( $order->get_id() ); ?>"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'infinitepay_verify' ) ); ?>">
				<?php esc_html_e( 'Verificar Pagamento na InfinitePay', 'infinitepay-woocommerce' ); ?>
			</button>
			<span class="infinitepay-verify-result" style="margin-left:8px;"></span>
		</div>
		<script>
		(function(){
			document.querySelector('.infinitepay-verify-btn').addEventListener('click', function(){
				var btn    = this;
				var result = btn.nextElementSibling;
				btn.disabled = true;
				result.textContent = '<?php echo esc_js( __( 'Verificando...', 'infinitepay-woocommerce' ) ); ?>';
				var data = new FormData();
				data.append('action',   'infinitepay_verify_payment');
				data.append('order_id', btn.dataset.orderId);
				data.append('_nonce',   btn.dataset.nonce);
				fetch(ajaxurl, { method: 'POST', body: data })
					.then(function(r){ return r.json(); })
					.then(function(json){
						result.textContent = json.data && json.data.message ? json.data.message : (json.success ? '<?php echo esc_js( __( 'Pago!', 'infinitepay-woocommerce' ) ); ?>' : '<?php echo esc_js( __( 'Não identificado.', 'infinitepay-woocommerce' ) ); ?>');
						btn.disabled = false;
						if(json.success){ location.reload(); }
					});
			});
		})();
		</script>
		<?php
	}

	public function ajax_verify(): void {
		check_ajax_referer( 'infinitepay_verify', '_nonce' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permissão negada.', 'infinitepay-woocommerce' ) ] );
		}

		$order_id = absint( $_POST['order_id'] ?? 0 );
		$order    = wc_get_order( $order_id );

		if ( ! $order ) {
			wp_send_json_error( [ 'message' => __( 'Pedido não encontrado.', 'infinitepay-woocommerce' ) ] );
		}

		$order_nsu = OrderHelper::get_meta( $order, OrderMetaKeys::ORDER_NSU );

		if ( ! $order_nsu ) {
			wp_send_json_error( [ 'message' => __( 'NSU não encontrado para este pedido.', 'infinitepay-woocommerce' ) ] );
		}

		$transaction_nsu = OrderHelper::get_meta( $order, OrderMetaKeys::TRANSACTION_NSU );
		$invoice_slug    = OrderHelper::get_meta( $order, OrderMetaKeys::INVOICE_SLUG );

		$check = $this->payment_check->check( $this->handle, $order_nsu, $transaction_nsu, $invoice_slug );

		if ( is_wp_error( $check ) ) {
			wp_send_json_error( [ 'message' => $check->get_error_message() ] );
		}

		if ( ! empty( $check['paid'] ) ) {
			OrderHelper::mark_as_processing( $order, __( 'Pagamento confirmado via verificação manual.', 'infinitepay-woocommerce' ) );
			wp_send_json_success( [ 'paid' => true, 'status' => 'processing', 'message' => __( 'Pagamento confirmado!', 'infinitepay-woocommerce' ) ] );
		}

		$lines = [
			'order_nsu: ' . $order_nsu,
			'handle: ' . ( $this->handle ?: '(não configurado)' ),
		];
		if ( $transaction_nsu ) {
			$lines[] = 'transaction_nsu: ' . $transaction_nsu;
		}
		wp_send_json_error( [ 'message' =>
			__( 'Pagamento não identificado na InfinitePay.', 'infinitepay-woocommerce' )
			. ' [' . implode( ' | ', $lines ) . ']'
		] );
	}
}
