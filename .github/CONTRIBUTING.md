# Contributing

## Setup

```bash
git clone https://github.com/marcvlima/infinitepay-woocommerce.git
cd infinitepay-woocommerce
composer install
```

Requirements: PHP 7.4+, Composer, WordPress test suite for running PHPUnit.

## Standards

- WordPress Coding Standards (WPCS) — run `composer phpcs`
- HPOS-compatible: never use `get_post_meta()` on orders
- All strings internationalised with `__()` / `esc_html__()` and domain `infinitepay-woocommerce`

## Running tests

```bash
composer test
```

## Pull requests

- Fork → branch → PR against `main`
- Fill in the PR template checklist
- One logical change per PR
