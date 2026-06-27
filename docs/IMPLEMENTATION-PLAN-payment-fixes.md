# Plano de Implementação — Correções de Confirmação de Pagamento InfinitePay

> Documento de trabalho para implementação. Pode ser removido após o merge.
> Todas as mudanças são no repositório do plugin `infinitepay-woocommerce`.

## Contexto / causa-raiz (validado empiricamente)

Pedidos pagos (inclusive Pix) ficavam presos em **"Pagamento Pendente"**, e o botão
"Verificar Pagamento" sempre retornava "não identificado". Causas confirmadas:

1. **BUG raiz** — `PaymentCheckEndpoint` envia o campo `invoice_slug` no body do
   `/payment_check`, mas a API exige **`slug`**. Teste empírico com pedido real #192:
   - `{handle, order_nsu, transaction_nsu, slug}` → `{"success":true,"paid":true,...}`
   - mesma chamada com `invoice_slug` → `{"success":false}`
   - O `/payment_check` exige os **4 campos**; faltando qualquer um → `success:false`.
2. **Webhook frágil** — o `WebhookValidator` fazia back-call síncrona ao `/payment_check`
   como pré-condição; se falhasse, retornava HTTP 400 e o pedido nunca era confirmado.
3. **capture_method** não era persistido quando a confirmação vinha por botão/cron.
4. **UX** — a página "Pedido recebido" não comunicava o status do pagamento e o link de
   comprovante era texto cru, sem identidade da marca.

### Contrato real do `/payment_check` (referência)
- Request body (todos obrigatórios): `handle`, `order_nsu`, `transaction_nsu`, **`slug`**.
- Nome do campo da fatura é **`slug`** no request (a InfinitePay usa `invoice_slug` no
  webhook e `slug` no redirect e no payment_check — inconsistência da própria API).
- `transaction_id` e `transaction_nsu` chegam no redirect com **valor idêntico**;
  `transaction_id` é alias não-documentado — **não usar**. Usar só `transaction_nsu`.
- Response: `{ success, paid, amount, paid_amount, installments, capture_method }`.

### Decisão registrada
- `order_nsu` permanece no formato `WC-{id}-{timestamp}` (unicidade por tentativa).
  **Não alterar.**

---

## Escopo — 8 arquivos

| # | Arquivo | Tipo de mudança |
|---|---------|-----------------|
| 1 | `src/Api/PaymentCheckEndpoint.php` | 1 linha — `invoice_slug` → `slug` |
| 2 | `src/Webhooks/WebhookValidator.php` | Reescrita — remove back-call |
| 3 | `src/Webhooks/WebhookHandler.php` | Reescrita — salva meta + valida paid_amount do payload + sempre 200 |
| 4 | `infinitepay-woocommerce.php` | 1 linha — instanciação do WebhookValidator |
| 5 | `src/PaymentRecovery/AdminVerifyButton.php` | Método `ajax_verify` — idempotência + slug + capture_method + diagnóstico |
| 6 | `src/PaymentRecovery/CronChecker.php` | `check_pending_orders` — slug + capture_method + cancelamento seguro |
| 7 | `src/PaymentRecovery/ReturnHandler.php` | Reescrita — capture_method + banner de status (UX) |
| 8 | `docs/ARCHITECTURE.md` | Atualizar diagramas + identificadores + gotcha do slug |

---

## Mudança 1 — `src/Api/PaymentCheckEndpoint.php`

**Única alteração.** No método `check()`, trocar o nome do campo:

```php
// ANTES
if ( $slug ) {
    $body['invoice_slug'] = $slug;
}

// DEPOIS
if ( $slug ) {
    $body['slug'] = $slug;
}
```

Nada mais muda neste arquivo. Esta correção sozinha conserta botão, cron e retorno.

---

## Mudança 2 — `src/Webhooks/WebhookValidator.php` (conteúdo final completo)

```php
<?php

namespace InfinitePay\WooCommerce\Webhooks;

use InfinitePay\WooCommerce\Logger;
use InfinitePay\WooCommerce\Order\OrderHelper;
use WC_Order;
use WP_Error;

defined( 'ABSPATH' ) || exit;

class WebhookValidator {

	private $logger;

	public function __construct( Logger $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Valida estrutura do payload, existência do pedido, método e idempotência.
	 * NÃO faz back-call ao /payment_check — a verificação de valor é feita pelo
	 * WebhookHandler a partir do paid_amount do próprio payload.
	 *
	 * @return WC_Order|WP_Error
	 */
	public function validate( array $payload ) {
		if ( empty( $payload['order_nsu'] ) ) {
			return new WP_Error( 'infinitepay_missing_nsu', 'Missing order_nsu in webhook payload.' );
		}

		$order = OrderHelper::find_order_by_nsu( $payload['order_nsu'] );
		if ( ! $order ) {
			return new WP_Error( 'infinitepay_order_not_found', 'Order not found for NSU: ' . $payload['order_nsu'] );
		}

		$method = $order->get_payment_method();
		if ( ! in_array( $method, [ 'infinitepay_checkout', 'infinitepay_pix' ], true ) ) {
			return new WP_Error( 'infinitepay_wrong_gateway', 'Order payment method is not InfinitePay.' );
		}

		if ( in_array( $order->get_status(), [ 'processing', 'completed' ], true ) ) {
			return new WP_Error( 'infinitepay_already_paid', 'Order already processed.' );
		}

		return $order;
	}
}
```

---

## Mudança 3 — `src/Webhooks/WebhookHandler.php` (conteúdo final completo)

```php
<?php

namespace InfinitePay\WooCommerce\Webhooks;

use InfinitePay\WooCommerce\Logger;
use InfinitePay\WooCommerce\Order\OrderHelper;
use WP_REST_Request;
use WP_REST_Response;

defined( 'ABSPATH' ) || exit;

class WebhookHandler {

	private $validator;
	private $logger;

	public function __construct( WebhookValidator $validator, Logger $logger ) {
		$this->validator = $validator;
		$this->logger    = $logger;
	}

	public function register_routes(): void {
		register_rest_route(
			'infinitepay/v1',
			'/webhook',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'handle' ],
				'permission_callback' => '__return_true',
			]
		);
	}

	public function handle( WP_REST_Request $request ): WP_REST_Response {
		$payload = json_decode( $request->get_body(), true );

		$this->logger->info( 'Webhook received.' );

		if ( ! is_array( $payload ) ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Invalid payload.' ], 400 );
		}

		$order = $this->validator->validate( $payload );

		if ( is_wp_error( $order ) ) {
			$code   = $order->get_error_code();
			// Idempotência — já pago não é erro do ponto de vista da InfinitePay.
			$status = 'infinitepay_already_paid' === $code ? 200 : 400;
			$this->logger->warning( 'Webhook rejected: ' . $order->get_error_message() );
			return new WP_REST_Response( [ 'success' => false, 'message' => $order->get_error_message() ], $status );
		}

		// Persiste os metadados IMEDIATAMENTE — garante transaction_nsu/slug para as
		// verificações posteriores (cron, botão admin, página de retorno).
		OrderHelper::save_infinitepay_meta(
			$order,
			[
				'TRANSACTION_NSU' => isset( $payload['transaction_nsu'] ) ? (string) $payload['transaction_nsu'] : '',
				'RECEIPT_URL'     => isset( $payload['receipt_url'] ) ? (string) $payload['receipt_url'] : '',
				'CAPTURE_METHOD'  => isset( $payload['capture_method'] ) ? (string) $payload['capture_method'] : '',
				'INSTALLMENTS'    => isset( $payload['installments'] ) ? (string) $payload['installments'] : '',
				'INVOICE_SLUG'    => isset( $payload['invoice_slug'] ) ? (string) $payload['invoice_slug'] : '',
			]
		);

		$paid_amount       = isset( $payload['paid_amount'] ) ? (int) $payload['paid_amount'] : 0;
		$order_total_cents = (int) round( $order->get_total() * 100 );

		// Tolerância de 1 centavo (arredondamento).
		if ( $paid_amount > 0 && $paid_amount >= ( $order_total_cents - 1 ) ) {
			$capture_method = isset( $payload['capture_method'] ) ? (string) $payload['capture_method'] : 'checkout';
			OrderHelper::mark_as_processing(
				$order,
				sprintf(
					/* translators: %s: método de captura (pix, credit_card, etc.) */
					__( 'Pagamento confirmado via InfinitePay (%s).', 'infinitepay-woocommerce' ),
					$capture_method
				)
			);
			do_action( 'infinitepay_payment_confirmed', $order, $payload );
			$this->logger->info( 'Webhook processed: order #' . $order->get_id() );
		} else {
			// paid_amount ausente/inconsistente — confirma 200 (evita retry da InfinitePay)
			// e agenda verificação por cron como rede de segurança.
			$this->logger->warning(
				sprintf(
					'Webhook: paid_amount inconsistente p/ pedido #%d (esperado %d, recebido %d). Verificação por cron agendada.',
					$order->get_id(),
					$order_total_cents,
					$paid_amount
				)
			);
			wp_schedule_single_event( time() + 30, 'infinitepay_check_pending_payments' );
		}

		return new WP_REST_Response( [ 'success' => true, 'message' => null ], 200 );
	}
}
```

---

## Mudança 4 — `infinitepay-woocommerce.php`

Dentro do `add_action( 'plugins_loaded', ... )`, trocar **apenas** a linha de
instanciação do validator. As demais instanciações (`ReturnHandler`, `CronChecker`,
`AdminVerifyButton`) permanecem iguais — elas ainda usam `$payment_check` e `$handle`.

```php
// ANTES
$validator = new WebhookValidator( $payment_check, $logger, $handle );

// DEPOIS
$validator = new WebhookValidator( $logger );
```

> A variável `$payment_check` continua sendo usada pelas linhas seguintes — **não remover**.

---

## Mudança 5 — `src/PaymentRecovery/AdminVerifyButton.php`

Substituir **apenas o método `ajax_verify()`** pelo conteúdo abaixo. O restante do
arquivo (construtor, `register`, `render_button`) não muda.

```php
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

		// Idempotência — se já está pago, não consulta a API.
		if ( in_array( $order->get_status(), [ 'processing', 'completed' ], true ) ) {
			wp_send_json_success( [
				'paid'    => true,
				'status'  => $order->get_status(),
				'message' => __( 'Pagamento já confirmado para este pedido.', 'infinitepay-woocommerce' ),
			] );
		}

		$order_nsu = OrderHelper::get_meta( $order, OrderMetaKeys::ORDER_NSU );

		if ( ! $order_nsu ) {
			wp_send_json_error( [ 'message' => __( 'NSU não encontrado para este pedido.', 'infinitepay-woocommerce' ) ] );
		}

		$transaction_nsu = OrderHelper::get_meta( $order, OrderMetaKeys::TRANSACTION_NSU );
		$invoice_slug    = OrderHelper::get_meta( $order, OrderMetaKeys::INVOICE_SLUG );

		// 4º argumento (slug) é obrigatório no /payment_check — vem do meta INVOICE_SLUG.
		$check = $this->payment_check->check( $this->handle, $order_nsu, $transaction_nsu, $invoice_slug );

		if ( is_wp_error( $check ) ) {
			wp_send_json_error( [ 'message' => $check->get_error_message() ] );
		}

		if ( ! empty( $check['paid'] ) ) {
			if ( ! empty( $check['capture_method'] ) ) {
				OrderHelper::save_infinitepay_meta( $order, [ 'CAPTURE_METHOD' => (string) $check['capture_method'] ] );
			}
			OrderHelper::mark_as_processing( $order, __( 'Pagamento confirmado via verificação manual.', 'infinitepay-woocommerce' ) );
			wp_send_json_success( [ 'paid' => true, 'status' => 'processing', 'message' => __( 'Pagamento confirmado!', 'infinitepay-woocommerce' ) ] );
		}

		// Diagnóstico — mostra os identificadores usados na consulta.
		$diag = [
			'order_nsu: ' . $order_nsu,
			'handle: ' . ( $this->handle ?: '(não configurado)' ),
		];
		if ( $transaction_nsu ) {
			$diag[] = 'transaction_nsu: ' . $transaction_nsu;
		}
		if ( $invoice_slug ) {
			$diag[] = 'slug: ' . $invoice_slug;
		}
		wp_send_json_error( [ 'message' =>
			__( 'Pagamento não identificado na InfinitePay.', 'infinitepay-woocommerce' )
			. ' [' . implode( ' | ', $diag ) . ']'
		] );
	}
```

> O arquivo já importa `OrderHelper` e `OrderMetaKeys` no topo — não precisa adicionar `use`.

---

## Mudança 6 — `src/PaymentRecovery/CronChecker.php`

Substituir **apenas o método `check_pending_orders()`** pelo conteúdo abaixo. Acrescenta:
passar `transaction_nsu` + `slug`, persistir `capture_method`, e **cancelamento seguro**
(só cancela após 24h se houver identificadores para verificar — evita cancelar pedido
pago que não pôde ser confirmado).

```php
	public function check_pending_orders(): void {
		$orders = OrderHelper::get_pending_orders_older_than( 15 );

		foreach ( $orders as $order ) {
			$order_nsu = OrderHelper::get_meta( $order, OrderMetaKeys::ORDER_NSU );

			if ( ! $order_nsu ) {
				continue;
			}

			$transaction_nsu = OrderHelper::get_meta( $order, OrderMetaKeys::TRANSACTION_NSU );
			$invoice_slug    = OrderHelper::get_meta( $order, OrderMetaKeys::INVOICE_SLUG );

			$check = $this->payment_check->check( $this->handle, $order_nsu, $transaction_nsu, $invoice_slug );

			if ( is_wp_error( $check ) ) {
				$this->logger->warning( 'CronChecker: error checking order #' . $order->get_id() . ': ' . $check->get_error_message() );
				continue;
			}

			if ( ! empty( $check['paid'] ) ) {
				if ( ! empty( $check['capture_method'] ) ) {
					OrderHelper::save_infinitepay_meta( $order, [ 'CAPTURE_METHOD' => (string) $check['capture_method'] ] );
				}
				OrderHelper::mark_as_processing( $order, __( 'Pagamento confirmado via verificação automática InfinitePay.', 'infinitepay-woocommerce' ) );
				$this->logger->info( 'CronChecker: order #' . $order->get_id() . ' marked as processing.' );
				continue;
			}

			// Cancelamento após 24h — SOMENTE se temos identificadores para verificar
			// de fato (transaction_nsu + slug). Sem eles, o /payment_check não consegue
			// confirmar e cancelar poderia descartar um pedido pago. Nesse caso, alerta
			// para revisão manual e mantém pendente.
			$created = $order->get_date_created();
			$expired = $created && ( time() - $created->getTimestamp() ) > DAY_IN_SECONDS;
			if ( $expired ) {
				if ( $transaction_nsu && $invoice_slug ) {
					OrderHelper::mark_as_cancelled( $order, __( 'Pedido cancelado automaticamente por falta de pagamento após 24h.', 'infinitepay-woocommerce' ) );
					$this->logger->info( 'CronChecker: order #' . $order->get_id() . ' cancelled after 24h.' );
				} else {
					$this->logger->warning( 'CronChecker: order #' . $order->get_id() . ' pendente >24h sem transaction_nsu/slug — revisão manual (NÃO cancelado).' );
				}
			}
		}
	}
```

> O arquivo já importa `OrderHelper` e `OrderMetaKeys` — não precisa adicionar `use`.

---

## Mudança 7 — `src/PaymentRecovery/ReturnHandler.php` (conteúdo final completo)

Inclui: confirmação via `/payment_check` (agora com slug correto após Mudança 1),
persistência do `capture_method` do retorno, e o **banner de status com a identidade da
marca** (ouro `#C8A153`, dark `#161616`, creme `#F0E6D0`) que substitui o link de
comprovante cru. Estilos inline para garantir renderização independente do tema.

```php
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
					$order = wc_get_order( $order_id ); // re-fetch para refletir o novo status
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
```

> Remove o antigo método `maybe_show_receipt()` (substituído pelo banner).

---

## Mudança 8 — `docs/ARCHITECTURE.md`

Atualizar para refletir o novo fluxo. Itens obrigatórios:

1. **Tabela de identificadores** (adicionar seção após "Directory Structure"):

   | Identificador | Quem cria | Exemplo | Uso |
   |---|---|---|---|
   | WC Order ID | WooCommerce | `192` | Chave do pedido no banco |
   | order_nsu | Plugin | `WC-192-1782528492` | Liga pedido WC ao link; formato `WC-{id}-{timestamp}` |
   | handle | Conta InfinitePay | `lucas-souza-tdo` | InfiniteTag sem `$`; identifica o vendedor |
   | transaction_nsu | InfinitePay | `e0bc15b7-...` | UUID da transação; redirect e webhook |
   | slug / invoice_slug | InfinitePay | `UMaS4Sotxo` | Código da fatura (ver gotcha abaixo) |

2. **Payment Flow (mermaid)** — remover a seta `WC->>IP: POST /payment_check (double-check)`
   do fluxo do webhook (o webhook não faz mais back-call).

3. **Payment Recovery Flow (mermaid)** — `D -- No --> F[Log + return 400]` passa a:
   webhook sempre retorna 200 p/ pedido válido; salva meta; valida paid_amount do payload;
   se ok → processing, senão agenda cron.

4. **Class Diagram** — remover `WebhookValidator --> PaymentCheckEndpoint`.

5. **Key Design Decisions** — adicionar bullets:
   - **Campo `slug` (não `invoice_slug`) no payment_check** — a InfinitePay usa nomes
     diferentes por superfície: webhook→`invoice_slug`, redirect→`slug`, payment_check
     request→`slug`. Confirmado por teste empírico.
   - **`transaction_id` ignorado** — alias não-documentado do `transaction_nsu` (mesmo
     valor); usar sempre `transaction_nsu`.
   - **Webhook sem back-call** — valida `paid_amount` do payload; sem chamada de saída.
   - **capture_method persistido em todos os caminhos** (webhook, retorno, botão, cron).
   - **Cancelamento por cron seguro** — só cancela após 24h se houver `transaction_nsu`
     + `slug` para verificar; senão mantém pendente p/ revisão manual.

---

## Validação (executar após implementar)

1. **Teste de contrato (sanidade)** — confirmar que o `/payment_check` responde `paid:true`
   apenas com `slug` (não `invoice_slug`). Já validado com pedido #192.
2. **Lint PHP** — `composer install` + (se houver) `composer run phpcs`/`phpunit`.
3. **Pagamento Pix ponta a ponta** no staging:
   - Pedido deve ir a "Processando" automaticamente (webhook) — log `Webhook processed: order #X`.
   - Página de obrigado deve mostrar o banner verde/ouro "Pagamento confirmado" + botão de comprovante estilizado.
4. **Botão "Verificar Pagamento"** em pedido pendente real → deve retornar "Pago!" e
   atualizar status; em pedido sem pagamento → mensagem de diagnóstico com order_nsu/handle/slug.
5. **Pedido já processando** → botão retorna "Pagamento já confirmado" sem chamar API.

---

## Git / entrega

- Trabalhar no repositório do plugin (`marcvlima/infinitepay-woocommerce`).
- O **PR #1** (`fix/webhook-resilience-and-payment-check`) já contém as Mudanças 2, 3, 4 e
  parte da 5/6 (sem o slug, sem capture_method, sem idempotência, sem UX). **Recomendado:**
  continuar nesse mesmo branch e adicionar as Mudanças 1, 5 (completa), 6 (completa), 7 e a
  atualização da 8 — fechando tudo num único PR. Atualizar título/descrição do PR.
- **Não** alterar o ponteiro do submodule no repo WordPress — o deploy puxa o HEAD da
  `main` do plugin via `git submodule update --remote` (ver PR #9 do repo WordPress).
- Após merge do PR do plugin: acionar deploy (staging via `workflow_dispatch`) e validar.
```
