---
title: Wallet Ledger
---

# Wallet Ledger

Wallet balances are tracked by wallet columns and ledger entries.

## Wallet Fields

| Field | Meaning |
| --- | --- |
| `available_balance_cents` | Spendable or withdrawable balance |
| `pending_balance_cents` | Pending bank transfer top-ups |

## Ledger Types

| Type | Direction | Description |
| --- | --- | --- |
| `topup` | `credit` | Wallet top-up |
| `guest_post_order` | `debit` | Advertiser pays for guest post order |
| `guest_post_payout` | `credit` | Publisher receives payout after approval |

## Idempotency

Payment and payout ledgers use `idempotency_key` to prevent duplicated wallet movement.
