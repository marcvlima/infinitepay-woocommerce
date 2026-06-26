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
│   │   └── WebhookValidator.php  Payload validation + amount check
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

## Payment Flow

```mermaid
sequenceDiagram
    participant C as Customer
    participant WC as WooCommerce
    participant IP as InfinitePay API

    C->>WC: Place order
    WC->>IP: POST /links (handle, items, order_nsu, redirect_url, webhook_url)
    IP-->>WC: { url: "https://checkout.infinitepay.io/..." }
    WC->>C: Redirect (transition screen or modal)
    C->>IP: Complete payment
    IP->>WC: POST /wp-json/infinitepay/v1/webhook
    WC->>IP: POST /payment_check (double-check)
    IP-->>WC: { paid: true, paid_amount: ... }
    WC->>WC: mark_as_processing()
    WC->>C: Order confirmation email
```

---

## Payment Recovery Flow

```mermaid
flowchart TD
    A[Order placed — status: pending] --> B{Webhook received?}
    B -- Yes --> C[WebhookValidator]
    C --> D{Valid + paid?}
    D -- Yes --> E[mark_as_processing ✅]
    D -- No --> F[Log + return 400]
    B -- No --> G{Customer returns\nto thank-you page?}
    G -- Yes --> H[ReturnHandler double-check]
    H --> D
    G -- No --> I{Cron runs\nevery 15 min}
    I --> J[CronChecker checks\nall pending orders]
    J --> D
    J --> K{Pending > 24h?}
    K -- Yes --> L[mark_as_cancelled ❌]
    K -- No --> M[Keep pending, check again]
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
        +check(handle, order_nsu, ...) array|WP_Error
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
    WebhookValidator --> PaymentCheckEndpoint
    WebhookValidator --> OrderHelper
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
- **Amount tolerance** — tolerância de 1 centavo entre `paid_amount` e total do pedido (arredondamento)
- **Handle masking** — Logger mascara a InfiniteTag em todos os logs (mostra 3 chars + `***`)
- **No card data** — o plugin nunca manipula dados de cartão; o checkout hospedado da InfinitePay faz isso
- **Triple payment recovery** — webhook + return handler + cron garantem que nenhum pagamento fica sem confirmação
