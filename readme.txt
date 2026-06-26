=== InfinitePay for WooCommerce ===
Contributors: marcvlima
Tags: payment, gateway, infinitepay, pix, woocommerce, brazil, checkout
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Integração do WooCommerce com o Checkout Integrado da InfinitePay via InfiniteTag — sem API Key.

== Description ==

Aceite pagamentos via InfinitePay diretamente no seu WooCommerce usando apenas sua **InfiniteTag** (handle). Sem geração de API Key, sem configurações complexas.

**Funcionalidades:**

* Pagamento via Checkout Integrado InfinitePay (Pix, Crédito, Débito)
* Identificação por InfiniteTag — nenhuma API Key necessária
* Tela de transição customizável (logo, cores, mensagem, delay)
* Modal overlay com fallbacks automáticos
* Webhook REST com validação e idempotência
* Verificação automática via cron a cada 15 minutos
* Compatível com HPOS e WooCommerce Blocks
* Tradução PT-BR completa

== Installation ==

1. Faça upload do plugin para `/wp-content/plugins/infinitepay-woocommerce/`
2. Execute `composer install` no diretório do plugin
3. Ative o plugin em **Plugins → Plugins instalados**
4. Acesse **WooCommerce → Configurações → Pagamentos → InfinitePay Checkout**
5. Insira sua InfiniteTag (sem o `$`) e salve

== Frequently Asked Questions ==

= Preciso gerar uma API Key? =
Não. A autenticação é feita apenas pela sua InfiniteTag.

= Funciona com o Checkout em Blocos do WooCommerce? =
Sim, compatível com WooCommerce Blocks.

= O que acontece se o webhook não chegar? =
O plugin verifica pedidos pendentes via cron a cada 15 minutos e também faz double-check quando o cliente retorna à loja.

== Changelog ==

= 1.0.0 =
* Lançamento inicial
* Integração com Checkout Integrado InfinitePay via InfiniteTag
* Gateways infinitepay_checkout e infinitepay_pix
* Tela de transição customizável
* Webhook REST com idempotência
* Compatibilidade HPOS e Blocks
* Tradução PT-BR

== Upgrade Notice ==

= 1.0.0 =
Lançamento inicial.
