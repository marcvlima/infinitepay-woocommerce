# InfinitePay API Reference

Base URL: `https://api.checkout.infinitepay.io`

## POST /links

Creates a payment link (Checkout Integrado).

**Request body:**
```json
{
  "handle": "lojamodelo",
  "order_nsu": "WC-123-1700000000",
  "redirect_url": "https://loja.com/checkout/order-received/123/",
  "webhook_url": "https://loja.com/wp-json/infinitepay/v1/webhook",
  "items": [
    { "quantity": 1, "price": 9900, "description": "Produto X" }
  ],
  "customer": {
    "name": "João Silva",
    "email": "joao@email.com",
    "phone_number": "11999999999"
  },
  "address": {
    "cep": "01310100",
    "number": "1000",
    "complement": "Apto 42"
  }
}
```

**Success response (200):**
```json
{ "url": "https://checkout.infinitepay.io/pay/abc123" }
```

---

## POST /payment_check

Checks whether a payment has been completed.

**Request body:**
```json
{
  "handle": "lojamodelo",
  "order_nsu": "WC-123-1700000000",
  "transaction_nsu": "uuid-opcional",
  "invoice_slug": "slug-opcional"
}
```

**Success response (200):**
```json
{
  "paid": true,
  "amount": 9900,
  "paid_amount": 9900,
  "installments": 1,
  "capture_method": "pix"
}
```

---

## Webhook (InfinitePay → your server)

InfinitePay sends a POST to your `webhook_url` after payment.

**Payload:**
```json
{
  "order_nsu": "WC-123-1700000000",
  "transaction_nsu": "uuid-transacao",
  "invoice_slug": "slug-fatura",
  "amount": 9900,
  "paid_amount": 9900,
  "installments": 1,
  "capture_method": "credit_card",
  "receipt_url": "https://comprovante.infinitepay.com/..."
}
```

Your webhook endpoint: `POST /wp-json/infinitepay/v1/webhook`
