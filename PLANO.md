# InfinitePay for WooCommerce — Plano de Execução

> **Repositório:** https://github.com/marcvlima/infinitepay-woocommerce  
> **Local:** `/Users/marcus/Developer/infinitepay-woocommerce/`  
> **Site principal:** `/Users/marcus/Developer/arabianmirage_wordpres/`  
> **Status:** 🔄 Em execução

---

## Legenda

- ✅ Concluído
- 🔄 Em andamento
- ⏳ Pendente (aguarda fase anterior)

---

## Estado do Repositório

- **GitHub:** https://github.com/marcvlima/infinitepay-woocommerce ✅ (criado e público)
- **Remote configurado:** `origin → https://github.com/marcvlima/infinitepay-woocommerce.git` ✅
- **Arquivos criados até agora:** `composer.json`, `.gitignore`, `.editorconfig`, `.phpcs.xml.dist`
- **Commit inicial:** ⏳ Pendente

---

## FASE 1: Scaffold + Repositório Público

> Status global: 🔄 Em andamento

| Task | Arquivo/Ação | Status |
|---|---|---|
| 1.1 | Criar diretório `/Users/marcus/Developer/infinitepay-woocommerce/` | ✅ |
| 1.2 | `git init` | ✅ |
| 1.3 | `gh repo create marcvlima/infinitepay-woocommerce --public` | ✅ |
| 1.4 | `git remote add origin https://github.com/marcvlima/infinitepay-woocommerce.git` | ✅ |
| 1.5 | `composer.json` (PSR-4 autoloader `InfinitePay\WooCommerce\ → src/`) | ✅ |
| 1.6 | `.gitignore` | ✅ |
| 1.7 | `LICENSE` (GPLv2) | ✅ |
| 1.8 | `.editorconfig` (tabs PHP, UTF-8) | ✅ |
| 1.9 | `.phpcs.xml.dist` (WordPress-Extra + WooCommerce rules) | ✅ |
| 1.10 | `composer install` → gera `vendor/autoload.php` | ⏳ |
| 1.11 | Criar estrutura de diretórios | ⏳ |
| 1.12 | `git add . && git commit -m "chore: project scaffold" && git push -u origin main` | ⏳ |

---

## FASE 2: Foundation

> Status global: ⏳ Aguarda Fase 1

| Task | Arquivo | Responsabilidade | Linhas est. |
|---|---|---|---|
| 2.1 | `src/Logger.php` | Wrapper `wc_get_logger()`. Métodos: `debug()`, `info()`, `warning()`, `error()`. Source: `infinitepay-woocommerce`. Mascaramento de `handle` nos logs. Nunca loga dados de cartão. | ~80 |
| 2.2 | `src/Order/OrderMetaKeys.php` | Classe de constantes: `CHECKOUT_URL`, `ORDER_NSU`, `TRANSACTION_NSU`, `RECEIPT_URL`, `CAPTURE_METHOD`, `INSTALLMENTS`, `INVOICE_SLUG`, `PAYMENT_CHECKED_AT` | ~30 |
| 2.3 | `src/Api/Client.php` | HTTP client com `wp_remote_post()` / `wp_remote_get()`. Base URL `https://api.checkout.infinitepay.io`. Headers `Content-Type: application/json`. Timeout 30s. Método `post(string $endpoint, array $body): array\|WP_Error`. Logging de request e response. Tratamento de erros HTTP com `WP_Error`. | ~120 |
| 2.4 | `src/Order/OrderHelper.php` | HPOS-compatible. Usa APENAS `$order->get_meta()`, `$order->update_meta_data()`, `$order->save()`. Métodos: `find_order_by_nsu()`, `save_infinitepay_meta()`, `get_meta()`, `mark_as_processing()`, `mark_as_cancelled()`, `get_pending_orders_older_than()` | ~150 |
| 2.5 | `git add . && git commit -m "feat(fase-2): Logger, OrderMetaKeys, Client, OrderHelper" && git push origin main` | — | — |

---

## FASE 3: API Endpoints

> Status global: ⏳ Aguarda Fase 2

| Task | Arquivo | Responsabilidade | Linhas est. |
|---|---|---|---|
| 3.1 | `src/Api/CheckoutEndpoint.php` | Método `create_link(array $payload): array\|WP_Error`. Valida campos obrigatórios: `handle`, `items`, `redirect_url`. Chama `Client::post('/links', $payload)`. Retorna `['url' => '...']` ou `WP_Error`. | ~80 |
| 3.2 | `src/Api/PaymentCheckEndpoint.php` | Método `check(string $handle, string $order_nsu, ...): array\|WP_Error`. Chama `Client::post('/payment_check', [...])`. Retorna `['paid' => bool, 'amount' => int, 'paid_amount' => int, 'installments' => int, 'capture_method' => string]`. | ~70 |
| 3.3 | `git add . && git commit -m "feat(fase-3): CheckoutEndpoint, PaymentCheckEndpoint" && git push origin main` | — | — |

---

## FASE 4: Business Logic Layer

> Status global: ⏳ Aguarda Fase 3

| Task | Arquivo | Responsabilidade | Linhas est. |
|---|---|---|---|
| 4.1 | `src/Gateways/AbstractGateway.php` | Estende `WC_Payment_Gateway`. Campos base: `enabled`, `title`, `description`, `handle`, `debug`. `is_available()` retorna false se handle vazio. Helpers: `get_handle()`, `is_debug()`. Instancia `Logger` e `Client`. | ~180 |
| 4.2 | `src/Admin/Settings.php` | Campos da tela de transição. `get_redirect_settings()`. Campos: `redirect_screen_enabled`, `redirect_logo`, `redirect_message`, `redirect_fallback_text`, `redirect_security_text`, `redirect_bg_color`, `redirect_text_color`, `redirect_accent_color`, `redirect_delay_seconds`. Sanitização adequada. | ~200 |
| 4.3 | `src/Webhooks/WebhookValidator.php` | Método `validate(array $payload): WC_Order\|WP_Error`. Valida: `order_nsu` presente, pedido existe, método de pagamento correto, idempotência, double-check via `PaymentCheckEndpoint`, `paid === true`, `paid_amount >= total`. | ~130 |
| 4.4 | `src/Checkout/RedirectScreen.php` | Método `render(string $checkout_url): void`. Coleta settings, resolve logo, chama template via `wc_get_template()`, faz enqueue de CSS/JS. | ~100 |
| 4.5 | `templates/checkout/redirect-screen.php` | Template HTML: fullscreen overlay, logo, spinner CSS, progress bar, mensagem, ícone cadeado, link fallback. Todos outputs escapados. | ~80 |
| 4.6 | `assets/css/redirect-screen.css` | Overlay fullscreen, flexbox, spinner, progress bar, responsivo, dark mode, CSS custom properties. | ~100 |
| 4.7 | `assets/js/redirect-screen.js` | Progress bar baseada em `delay`, `setTimeout` para redirect, fallback via HTML nativo. | ~40 |
| 4.8 | `src/Checkout/ModalHandler.php` | `enqueue_checkout_scripts()`. Registra `modal-handler.js`. `wp_localize_script()` com `thankyouUrl` e `nonce`. | ~60 |
| 4.9 | `assets/js/modal-handler.js` | Abre modal com iframe, detecta bloqueio, fallbacks (popup → redirect), escuta `postMessage`, fecha modal e redireciona ao confirmar pagamento. | ~120 |
| 4.10 | `git add . && git commit -m "feat(fase-4): business logic layer" && git push origin main` | — | — |

---

## FASE 5: Core Features

> Status global: ⏳ Aguarda Fase 4

| Task | Arquivo | Responsabilidade | Linhas est. |
|---|---|---|---|
| 5.1 | `src/Gateways/CheckoutGateway.php` | ID: `infinitepay_checkout`. `process_payment()`: monta payload, chama `CheckoutEndpoint::create_link()`, salva metas, define status `pending`, retorna redirect ou modal. | ~250 |
| 5.2 | `src/Gateways/PixGateway.php` | ID: `infinitepay_pix`. Title: "Pague com Pix". Mesma lógica de `process_payment()`. | ~100 |
| 5.3 | `src/Webhooks/WebhookHandler.php` | REST route `POST /infinitepay/v1/webhook`. Valida payload via `WebhookValidator`, salva metas, `mark_as_processing()`, dispara action `infinitepay_payment_confirmed`. | ~130 |
| 5.4 | `src/PaymentRecovery/CronChecker.php` | Agenda cron a cada 15min. Verifica pedidos pendentes, chama `PaymentCheckEndpoint`, processa ou cancela após 24h. | ~120 |
| 5.5 | `src/PaymentRecovery/ReturnHandler.php` | Hook `woocommerce_thankyou`. Captura query params, salva metas, chama `PaymentCheckEndpoint`, exibe receipt_url. | ~100 |
| 5.6 | `src/PaymentRecovery/AdminVerifyButton.php` | Botão admin "Verificar Pagamento". AJAX handler com nonce, `current_user_can()`, chama `PaymentCheckEndpoint`. | ~120 |
| 5.7 | `git add . && git commit -m "feat(fase-5): core features" && git push origin main` | — | — |

---

## FASE 6: Polish

> Status global: ⏳ Aguarda Fase 5

| Task | Arquivo | Responsabilidade | Linhas est. |
|---|---|---|---|
| 6.1 | `src/Blocks/BlockSupport.php` | Estende `AbstractPaymentMethodType`. `is_active()`, `get_payment_method_script_handles()`, `get_payment_method_data()`. | ~80 |
| 6.2 | `assets/js/checkout-blocks.js` | `registerPaymentMethod` para `infinitepay_checkout` com label, content, edit, canMakePayment, ariaLabel. | ~80 |
| 6.3 | `src/Admin/StatusPage.php` | Hook `woocommerce_system_status_report`. Exibe: handle, conectividade API, webhook URL, HPOS, Blocks, debug, PHP version, cron, último webhook. | ~120 |
| 6.4 | `assets/css/admin.css` | Estilos tabela status, botão verificar, color picker preview, logo preview. | ~60 |
| 6.5 | `uninstall.php` | Verifica `WP_UNINSTALL_PLUGIN`. Remove options. Limpa cron. Filter para remover metas. | ~40 |
| 6.6 | `infinitepay-woocommerce.php` | Entry point completo: header, constantes, autoload, HPOS/Blocks compatibility, instancia todos componentes, registra gateways, hooks ativação/desativação. | ~180 |
| 6.7 | `i18n/languages/infinitepay-woocommerce.pot` | Template POT com todas as strings. | — |
| 6.8 | `i18n/languages/infinitepay-woocommerce-pt_BR.po` | Tradução PT-BR completa. | — |
| 6.9 | `git add . && git commit -m "feat(fase-6): polish, entry point, i18n" && git push origin main` | — | — |

---

## FASE 7: Open Source Package

> Status global: ⏳ Aguarda Fase 6

| Task | Arquivo | Responsabilidade |
|---|---|---|
| 7.1 | `README.md` | Bilíngue PT-BR + EN. Badges, features, instalação, configuração, FAQ, segurança, licença. |
| 7.2 | `readme.txt` | Formato WordPress.org. Tags, tested up to, changelog, FAQ. |
| 7.3 | `docs/ARCHITECTURE.md` | Diagramas Mermaid da arquitetura e fluxo de pagamento. |
| 7.4 | `docs/API-REFERENCE.md` | Referência dos endpoints `/links` e `/payment_check`. |
| 7.5 | `docs/CHANGELOG.md` | v1.0.0 — Initial release. |
| 7.6 | `.github/ISSUE_TEMPLATE/bug_report.md` | Template: versões, passos, comportamento esperado vs atual, logs. |
| 7.7 | `.github/ISSUE_TEMPLATE/feature_request.md` | Template: descrição, caso de uso, alternativas. |
| 7.8 | `.github/PULL_REQUEST_TEMPLATE.md` | Checklist: PHPCS, testes, i18n, HPOS, sem credenciais hardcoded. |
| 7.9 | `.github/CONTRIBUTING.md` | Dev setup, padrões, processo de PR. |
| 7.10 | `.github/workflows/phpcs.yml` | CI: PHP 7.4–8.2, composer install, phpcs. |
| 7.11 | `.github/workflows/phpunit.yml` | CI: PHP 7.4–8.2, composer install, phpunit. |
| 7.12 | `tests/bootstrap.php` | Carrega WP test suite, mocks WC, autoloader. |
| 7.13 | `tests/phpunit.xml` | Configuração PHPUnit. |
| 7.14 | `tests/Unit/Api/ClientTest.php` | Testa `Client::post()` com mocks. |
| 7.15 | `tests/Unit/Api/CheckoutEndpointTest.php` | Testa `create_link()`. |
| 7.16 | `tests/Unit/Gateways/CheckoutGatewayTest.php` | Testa `process_payment()`. |
| 7.17 | `tests/Unit/Order/OrderHelperTest.php` | Testa helpers de pedido. |
| 7.18 | `tests/Unit/Webhooks/WebhookHandlerTest.php` | Testa processamento webhook. |
| 7.19 | `tests/Unit/Webhooks/WebhookValidatorTest.php` | Testa idempotência, amount mismatch, nsu inválido. |
| 7.20 | `tests/Unit/PaymentRecovery/CronCheckerTest.php` | Testa pedidos pendentes e cancelamento 24h. |
| 7.21 | `SECURITY.md` | Como reportar vulnerabilidades. |
| 7.22 | `CODE_OF_CONDUCT.md` | Contributor Covenant v2.1. |
| 7.23 | `assets/images/infinitepay-logo.svg` | Logo SVG. |
| 7.24 | `assets/images/infinitepay-icon.png` | Ícone 128x128px. |
| 7.25 | `git add . && git commit -m "docs+ci+test(fase-7): open source package" && git push origin main` | — |

---

## FASE 8: Integration & Testing (Sequencial)

> Status global: ⏳ Aguarda Fase 7

| Task | Descrição | Comando |
|---|---|---|
| 8.1 | Tag de release | `git tag -a v1.0.0 -m "Initial release" && git push origin v1.0.0` |
| 8.2 | Criar GitHub Release | `gh release create v1.0.0 --title "v1.0.0 — Initial Release" --notes-file docs/CHANGELOG.md` |
| 8.3 | Adicionar como submodule no site | `cd /Users/marcus/Developer/arabianmirage_wordpres && git submodule add https://github.com/marcvlima/infinitepay-woocommerce.git wp-content/plugins/infinitepay-woocommerce` |
| 8.4 | Copiar para container Docker | `docker cp wp-content/plugins/infinitepay-woocommerce recuperacaositeversaoatualarabianmirage-wordpress-1:/var/www/html/wp-content/plugins/` |
| 8.5 | Ativar plugin via WP-CLI | `docker compose run --rm wpcli wp plugin activate infinitepay-woocommerce` |
| 8.6 | Verificar ativação | `docker compose run --rm wpcli wp plugin list` |
| 8.7 | Testar configuração | Acessar `https://localhost/wp-admin` > WooCommerce > Pagamentos > InfinitePay |
| 8.8 | Teste E2E | Compra completa: carrinho → checkout → pagamento → confirmação |
| 8.9 | Commit do submodule no site | `cd /Users/marcus/Developer/arabianmirage_wordpres && git add . && git commit -m "feat: add infinitepay-woocommerce as submodule" && git push` |

---

## Verificação Final (20 testes E2E)

1. Plugin ativa sem erros fatais
2. Aviso amigável se WooCommerce desativado
3. Settings: handle salva corretamente e é validado
4. Settings: logo upload funciona
5. Settings: cores e mensagens customizáveis
6. StatusPage: conectividade API OK
7. StatusPage: webhook URL exibida corretamente
8. Checkout Classic: gateway aparece
9. Checkout Blocks: gateway aparece
10. Tela de transição: renderiza com logo e mensagem do site
11. Modal overlay: tenta abrir iframe (ou fallback redirect)
12. API InfinitePay: retorna link de pagamento (requer handle real)
13. Webhook simulado via curl: atualiza pedido para "processing"
14. Return URL: captura params e faz double-check
15. Cron: verifica pedidos pendentes após 15 minutos
16. Botão admin: verifica pagamento manualmente
17. Idempotência: webhook recebido 2x não duplica o processamento
18. Desinstalação: limpa options e cron do banco
19. HPOS: funciona com Compatibility Mode desativado
20. Template override: customização via tema funciona
