## Summary



## Checklist

- [ ] PHPCS passes (`composer phpcs`)
- [ ] Tests pass (`composer test`)
- [ ] All user-facing strings wrapped in `__()`  with `infinitepay-woocommerce` domain
- [ ] No `get_post_meta()` / `update_post_meta()` — uses HPOS-compatible `$order->get_meta()`
- [ ] No hardcoded credentials or handles
- [ ] Tested on Classic Checkout
- [ ] Tested on Blocks Checkout
