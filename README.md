# InfinitePay for WooCommerce

> Integração do WooCommerce com o **Checkout Integrado da InfinitePay** via InfiniteTag — sem API Key.

[![PHPUnit](https://github.com/marcvlima/infinitepay-woocommerce/actions/workflows/phpunit.yml/badge.svg)](https://github.com/marcvlima/infinitepay-woocommerce/actions/workflows/phpunit.yml)
[![PHPCS](https://github.com/marcvlima/infinitepay-woocommerce/actions/workflows/phpcs.yml/badge.svg)](https://github.com/marcvlima/infinitepay-woocommerce/actions/workflows/phpcs.yml)
[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)
[![WordPress 6.4+](https://img.shields.io/badge/WordPress-6.4%2B-21759b)](https://wordpress.org)
[![WooCommerce 8.0+](https://img.shields.io/badge/WooCommerce-8.0%2B-96588a)](https://woocommerce.com)
[![PHP 7.4+](https://img.shields.io/badge/PHP-7.4%2B-777bb4)](https://php.net)

---

## Funcionalidades

- Pagamento via **InfinitePay Checkout Integrado** (Pix, Crédito, Débito)
- Identificação por **InfiniteTag** — nenhuma API Key necessária
- **Tela de transição** customizável (logo, cores, mensagem, delay)
- **Modal overlay** com fallbacks automáticos (popup → redirect direto)
- **Webhook REST** com validação, idempotência e double-check de valor
- **Verificação automática** via cron a cada 15 minutos
- **Double-check** na página de retorno do cliente
- **Botão admin** para verificação manual por pedido
- Compatível com **HPOS** (High-Performance Order Storage)
- Compatível com **WooCommerce Blocks** Checkout
- Tradução **PT-BR** completa

---

## Fluxo de Pagamento

```
Cliente faz checkout
        │
        ▼
WooCommerce chama process_payment()
        │
        ▼
Plugin envia POST /links para InfinitePay
(handle, itens, order_nsu, redirect_url, webhook_url)
        │
        ▼
InfinitePay retorna URL de pagamento
        │
        ▼
Cliente é redirecionado (tela de transição ou modal)
        │
        ▼
Cliente paga na InfinitePay
        │
        ├──► InfinitePay chama webhook do site
        │           │
        │           ▼
        │    Plugin valida + double-check
        │           │
        │           ▼
        │    Pedido → processing ✅
        │
        └──► Cliente retorna à loja (URL de retorno)
                    │
                    ▼
             Plugin faz double-check
                    │
                    ▼
             Pedido → processing ✅ (se webhook ainda não chegou)
```

---

## Requisitos

| Dependência | Versão mínima |
|---|---|
| PHP | 7.4 |
| WordPress | 6.4 |
| WooCommerce | 8.0 |

---

## Instalação

### Via repositório (recomendado)

```bash
cd wp-content/plugins
git clone https://github.com/marcvlima/infinitepay-woocommerce.git
cd infinitepay-woocommerce
composer install
```

### Manual

1. Baixe o ZIP da [última release](https://github.com/marcvlima/infinitepay-woocommerce/releases/latest)
2. No painel WordPress: **Plugins → Adicionar novo → Enviar plugin**
3. Execute `composer install` no diretório do plugin

---

## Configuração

1. Abra o **App InfinitePay** → canto superior esquerdo → anote sua **InfiniteTag** (ex: `$lojamodelo`)
2. No app, acesse **Vendas → Checkout → Configurações** e ative a **Etapa de endereço**
3. No WordPress: **WooCommerce → Configurações → Pagamentos → InfinitePay Checkout**
4. Insira o handle **sem o `$`** (ex: `lojamodelo`) e salve
5. A URL do webhook é exibida em **WooCommerce → Status do Sistema → InfinitePay**

---

## Arquitetura

Veja [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) para diagramas detalhados.

```
src/
├── Api/              # HTTP client + wrappers dos endpoints InfinitePay
├── Admin/            # Settings + Status Page
├── Blocks/           # Integração com WooCommerce Blocks
├── Checkout/         # Tela de transição + Modal handler
├── Gateways/         # WC_Payment_Gateway (Checkout + Pix)
├── Order/            # Helpers HPOS-safe + constantes de meta keys
├── PaymentRecovery/  # Cron, ReturnHandler, AdminVerifyButton
├── Webhooks/         # REST route + validador de payload
└── Logger.php        # Wrapper do WC Logger com mascaramento de handle
```

---

## Desenvolvimento

```bash
composer install
composer phpcs    # verificar padrões de código
composer test     # rodar testes unitários
```

Veja [.github/CONTRIBUTING.md](.github/CONTRIBUTING.md) para o guia completo.

---

## FAQ

**Preciso gerar uma API Key na InfinitePay?**
Não. A autenticação é feita apenas pela sua InfiniteTag (handle).

**O plugin funciona com o novo Checkout em Blocos do WooCommerce?**
Sim, compatível com WooCommerce Blocks via `BlockSupport`.

**O que acontece se o webhook não chegar?**
O plugin verifica pedidos pendentes via cron a cada 15 minutos. Também há double-check quando o cliente retorna à loja.

**Como faço para customizar a tela de redirecionamento?**
Em WooCommerce → Pagamentos → InfinitePay Checkout, seção "Tela de Transição".

---

## Segurança

Veja [SECURITY.md](SECURITY.md) para reportar vulnerabilidades.

---

## Licença

GPL-2.0-or-later — veja [LICENSE](LICENSE).
