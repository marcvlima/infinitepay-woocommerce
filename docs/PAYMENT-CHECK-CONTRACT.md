# Contrato da API InfinitePay — `/payment_check` e identificadores

> Documentação do contrato real da API de Checkout da InfinitePay
> (`https://api.checkout.infinitepay.io`), descoberto por **teste empírico** com um
> pedido real — porque a documentação oficial é ambígua/inconsistente nestes pontos.

## `POST /payment_check`

**Os quatro campos do request body são obrigatórios** para retornar `paid:true`:

| Campo | Obrigatório | Observação |
|---|---|---|
| `handle` | ✅ | InfiniteTag do vendedor, sem o `$` |
| `order_nsu` | ✅ | Identificador do pedido enviado no `POST /links` |
| `transaction_nsu` | ✅ | UUID da transação (vem do webhook / redirect) |
| `slug` | ✅ | Código da fatura — **o campo se chama `slug`, NÃO `invoice_slug`** |

Faltando qualquer um dos quatro, a resposta é `{"success":false}`.

### ⚠️ Gotcha — `slug` vs `invoice_slug`

A própria InfinitePay usa **nomes diferentes para o mesmo dado** dependendo da superfície:

| Superfície | Nome do campo |
|---|---|
| Payload do **webhook** | `invoice_slug` |
| Query string do **redirect** de retorno | `slug` |
| Request body do **`/payment_check`** | `slug` |

Enviar `invoice_slug` no `/payment_check` retorna `{"success":false}`. Internamente o
plugin guarda o valor no meta `_infinitepay_invoice_slug`, mas ao montar o request do
`/payment_check` o campo **deve** se chamar `slug` (ver `src/Api/PaymentCheckEndpoint.php`).

### Exemplo (validado)

Request:
```json
{
  "handle": "lucas-souza-tdo",
  "order_nsu": "WC-192-1782528492",
  "transaction_nsu": "e0bc15b7-3c30-4898-b33a-765a85e6b4b3",
  "slug": "UMaS4Sotxo"
}
```

Response:
```json
{
  "success": true,
  "paid": true,
  "amount": 25980,
  "paid_amount": 25980,
  "installments": 1,
  "capture_method": "pix"
}
```

Confirma que o `/payment_check` **funciona para Pix** (`capture_method: "pix"`).

## `transaction_id` vs `transaction_nsu`

No redirect de retorno, a InfinitePay envia **ambos** os parâmetros com **valor
idêntico** (mesmo UUID). A documentação oficial só documenta `transaction_nsu`.

`transaction_id` é um **alias não-documentado** — sem diferença funcional. **Usar sempre
`transaction_nsu`**; o plugin ignora `transaction_id` de propósito.

## Parâmetros do redirect de retorno (`redirect_url`)

A InfinitePay anexa à query string: `order_nsu`, `transaction_nsu`, `slug`,
`capture_method`, `receipt_url` (e o alias `transaction_id`).

## Autenticação

O endpoint **não** usa API key nem Bearer token — o `handle` no payload identifica o
vendedor. A proteção contra abuso vem da verificação ativa de pagamento e da conferência
de `paid_amount` contra o total do pedido.
