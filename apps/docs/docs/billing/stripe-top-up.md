---
title: Stripe Top-Up
---

# Stripe Top-Up

Stripe top-up uses hosted Checkout Sessions.

## Environment Variables

Required:

```text
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
```

## Webhook

Live endpoint:

```text
https://ads.mcv.network/stripe/webhook
```

Handled events:

- `checkout.session.completed`
- `checkout.session.async_payment_succeeded`

## Production Safety

In production, webhook requests require Stripe signature verification.

If `STRIPE_WEBHOOK_SECRET` is missing, webhook requests fail instead of accepting unsigned payloads.
