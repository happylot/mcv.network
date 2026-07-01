---
title: Top Up Wallet
---

# Top Up Wallet

Advertisers can add funds from `/billing`.

## Stripe Checkout

Stripe Checkout is the preferred top-up method.

Current behavior:

- User enters amount.
- Portal creates a Stripe Checkout Session.
- User pays on Stripe-hosted checkout.
- On successful payment, wallet `available_balance_cents` is increased.
- Stripe webhook also handles asynchronous confirmation.

## Bank Transfer

Bank transfer creates a pending top-up request.

Current behavior:

- Payment status: `pending`
- Ledger entry status: `pending`
- Wallet `pending_balance_cents` is increased.
- Admin reconciliation is still manual.

## Test Card

For Stripe test mode, use:

```text
4242 4242 4242 4242
```

Use any future expiry date and any CVC.
