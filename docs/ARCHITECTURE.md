# Architecture

## Overview

This plugin integrates WooCommerce with InfinitePay's **Checkout Integrado** (Link Integrado) API.
Authentication uses only the merchant's **InfiniteTag** (handle) — no API key required.

---

## Directory Structure

```
infinitepay-woocommerce/
├── src/
│   ├── Api/                  # HTTP client + endpoint wrappers
│   │   ├── Client.php            wp_remote_post/get wrapper
│   │   ├── CheckoutEndpoint.php  POST /links
│   │   └── PaymentCheckEndpoint.php  POST /payment_check
│   ├── Admin/
│   │   ├── Settings.php          Redirect screen settings fields
│   │   └── StatusPage.php        WooCommerce system status report
│   ├── Blocks/
│   │   └── BlockSupport.php      WooCommerce Blocks integration
│   ├── Checkout/
│   │   ├── RedirectScreen.php    Renders transition screen
│   │   └── ModalHandler.php      Enqueues modal JS
│   ├── Gateways/
│   │   ├── AbstractGateway.php   Base WC_Payment_Gateway
│   │   ├── CheckoutGateway.php   infinitepay_checkout
│   │   └── PixGateway.php        infinitepay_pix
│   ├── Order/
│   │   ├── OrderMetaKeys.php     Meta key constants
│   │   └── OrderHelper.php       HPOS-safe order helpers
│   ├── PaymentRecovery/
│   │   ├── CronChecker.php       15-min cron job
│   │   ├── ReturnHandler.php     Thank-you page double-check
│   │   └── AdminVerifyButton.php Manual verify button in order screen
│   ├── Webhooks/
│   │   ├── WebhookHandler.php    REST POST /infinitepay/v1/webhook
│   │   └── WebhookValidator.php  Payload structure + order lookup
│   └── Logger.php                WC logger wrapper, masks handle
├── assets/
│   ├── css/redirect-screen.css
│   ├── css/admin.css
│   ├── js/redirect-screen.js
│   ├── js/modal-handler.js
│   └── js/checkout-blocks.js
├── templates/checkout/
│   └── redirect-screen.php       Overridable template
├── i18n/languages/               PT-BR translations
├── tests/Unit/                   PHPUnit test suites
└── infinitepay-woocommerce.php   Plugin entry point
```

---

## Identificadores de pagamento

| Identificador | Quem cria | Exemplo | Uso |
|---|---|---|---|
| **WC Order ID** | WooCommerce | `191` | Chave primária do pedido no banco |
| **order_nsu** | Plugin | `WC-191-1750978260` | Enviado no `POST /links`; liga o pedido WC ao link InfinitePay |
| **handle** | Conta InfinitePay | `arabianmirage` | InfiniteTag sem o `$`; identifica o vendedor em todos os endpoints |
| **transaction_nsu** | InfinitePay | `550e8400-e29b-...` | UUID gerado pela InfinitePay ao processar o pagamento; retornado no webhook e na redirect_url |
| **invoice_slug** | InfinitePay | `abc123` | Código da fatura; retornado no webhook e na redirect_url |

---

## Payment Flow

```mermaid
sequenceDiagram
    participant C as Customer
    participant WC as WooCommerce
    participant IP as InfinitePay API

    C->>WC: Place order
    WC->>IP: POST /links (handle, order_nsu, items, redirect_url, webhook_url)
    IP-->>WC: { url: "https://checkout.infinitepay.io/..." }
    WC->>C: Redirect to InfinitePay checkout
    C->>IP: Complete payment (Pix, credit card, etc.)
    IP->>WC: POST /wp-json/infinitepay/v1/webhook\n{ order_nsu, transaction_nsu, paid_amount, ... }
    WC->>WC: Validate order + save meta + check paid_amount
    WC->>WC: mark_as_processing()
    WC->>C: Order confirmation email
    IP->>C: Redirect to redirect_url?transaction_nsu=...&slug=...
    Note over WC: ReturnHandler fires on thank-you page\nas a secondary confirmation path
```

---

## Payment Recovery Flow

```mermaid
flowchart TD
    A[Order placed — status: pending] --> B{Webhook received?}

    B -- Yes --> C[WebhookValidator\ncheck order + method + idempotency]
    C --> D{Order found\n& still pending?}
    D -- No --> F[Return 400 — order not found\nor wrong method]
    D -- Yes --> G[Save meta immediately\ntransaction_nsu / invoice_slug / etc.]
    G --> H{paid_amount in payload\n≥ order total?}
    H -- Yes --> E[mark_as_processing ✅\nReturn 200]
    H -- No --> I[Schedule cron check +30s\nReturn 200]

    B -- No --> J{Customer returns\nto thank-you page?}
    J -- Yes --> K[ReturnHandler\nPOST /payment_check\nwith order_nsu + transaction_nsu from URL]
    K --> L{paid?}
    L -- Yes --> E
    L -- No --> M[Keep pending]

    J -- No --> N{Cron runs\nevery 15 min}
    I --> N
    N --> O[CronChecker checks all pending orders\nPOST /payment_check per order\nusing stored order_nsu + transaction_nsu]
    O --> P{paid?}
    P -- Yes --> E
    P -- No --> Q{Pending > 24h?}
    Q -- Yes --> R[mark_as_cancelled ❌]
    Q -- No --> S[Keep pending, check again]
```

---

## Class Diagram

```mermaid
classDiagram
    class AbstractGateway {
        #Logger logger
        #Client client
        +get_handle() string
        +is_debug() bool
        +is_available() bool
    }
    class CheckoutGateway {
        +id = infinitepay_checkout
        +process_payment(order_id) array
    }
    class PixGateway {
        +id = infinitepay_pix
    }
    class Client {
        +post(endpoint, body) array|WP_Error
        +get(endpoint) array|WP_Error
    }
    class CheckoutEndpoint {
        +create_link(payload) array|WP_Error
    }
    class PaymentCheckEndpoint {
        +check(handle, order_nsu, transaction_nsu, invoice_slug) array|WP_Error
    }
    class WebhookValidator {
        +validate(payload) WC_Order|WP_Error
    }
    class WebhookHandler {
        +register_routes() void
        +handle(request) WP_REST_Response
    }
    class OrderHelper {
        +find_order_by_nsu(nsu) WC_Order
        +save_infinitepay_meta(order, data) void
        +mark_as_processing(order, note) void
        +mark_as_cancelled(order, reason) void
        +get_pending_orders_older_than(min) array
    }
    class CronChecker {
        +register() void
        +check_pending_orders() void
    }
    class ReturnHandler {
        +handle_return(order_id) void
    }
    class AdminVerifyButton {
        +render_button(order) void
        +ajax_verify() void
    }

    AbstractGateway <|-- CheckoutGateway
    CheckoutGateway <|-- PixGateway
    AbstractGateway --> Client
    CheckoutGateway --> CheckoutEndpoint
    CheckoutEndpoint --> Client
    PaymentCheckEndpoint --> Client
    WebhookHandler --> WebhookValidator
    WebhookValidator --> OrderHelper
    WebhookHandler --> OrderHelper
    CronChecker --> PaymentCheckEndpoint
    CronChecker --> OrderHelper
    ReturnHandler --> PaymentCheckEndpoint
    ReturnHandler --> OrderHelper
    AdminVerifyButton --> PaymentCheckEndpoint
    AdminVerifyButton --> OrderHelper
```

---

## Key Design Decisions

- **HPOS-compatible** — todo acesso a meta via `$order->get_meta()` / `update_meta_data()`, nunca `get_post_meta()`
- **Idempotent webhook** — pedidos já em `processing`/`completed` retornam HTTP 200 sem reprocessar
- **Webhook sem back-call** — o `WebhookHandler` valida o `paid_amount` diretamente do payload InfinitePay; não faz chamada de saída para `/payment_check` durante o webhook, eliminando o risco de deadlock por timeout ou falha de rede
- **Meta salvo imediatamente no webhook** — `transaction_nsu`, `invoice_slug` e demais campos são gravados antes de qualquer validação de valor, para que `CronChecker`, `ReturnHandler` e `AdminVerifyButton` possam usá-los nas verificações posteriores
- **Amount tolerance** — tolerância de 1 centavo entre `paid_amount` e total do pedido (arredondamento)
- **Fallback por cron quando paid_amount ausente** — se o webhook não incluir `paid_amount` válido, o handler retorna 200 (para evitar retry desnecessário da InfinitePay) e agenda verificação via `/payment_check` em 30 segundos
- **Handle masking** — Logger mascara a InfiniteTag em todos os logs (mostra 3 chars + `***`)
- **No card data** — o plugin nunca manipula dados de cartão; o checkout hospedado da InfinitePay faz isso
- **Triple payment recovery** — webhook + return handler + cron garantem que nenhum pagamento fica sem confirmação; `CronChecker` e `AdminVerifyButton` usam `transaction_nsu` armazenado no meta quando disponível, aumentando a taxa de sucesso na verificação via `/payment_check`
