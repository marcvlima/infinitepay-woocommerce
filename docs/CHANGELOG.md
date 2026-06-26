# Changelog

## [1.0.0] - 2024-01-01

### Added
- InfinitePay Checkout Integrado via InfiniteTag (sem necessidade de API Key)
- Gateway `infinitepay_checkout` com suporte a todos os métodos InfinitePay
- Gateway `infinitepay_pix` dedicado ao pagamento via Pix
- Tela de transição customizável (logo, cores, mensagem, delay)
- Modal overlay com fallback para popup e redirect direto
- Webhook REST `POST /infinitepay/v1/webhook` com validação e idempotência
- Verificação automática via cron a cada 15 minutos
- Double-check de pagamento na página de retorno
- Botão "Verificar Pagamento" no painel do pedido (admin)
- Compatibilidade com HPOS (High-Performance Order Storage)
- Compatibilidade com WooCommerce Blocks Checkout
- Status Page no relatório de sistema do WooCommerce
- Tradução PT-BR completa
- Uninstall limpo (remove options e cron)
