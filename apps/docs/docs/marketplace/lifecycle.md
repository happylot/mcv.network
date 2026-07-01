---
title: Marketplace Lifecycle
---

# Guest Post Marketplace Lifecycle

```text
Publisher submits website
  -> Admin approves website
  -> Advertiser buys guest post
  -> Publisher submits published URL
  -> Advertiser or admin approves fulfillment
  -> Publisher payout is released
```

## Data Objects

| Object | Purpose |
| --- | --- |
| `PublisherWebsite` | Publisher inventory |
| `GuestPostOrder` | Advertiser purchase and fulfillment |
| `WalletLedgerEntry` | Money movement audit trail |

## Money Movement

1. Advertiser buys order: advertiser wallet is debited immediately.
2. Publisher fulfills order: no money moves yet.
3. Order is approved: publisher wallet is credited.

This creates a simple escrow-like flow until a dedicated escrow ledger is added.

## Agency Service Lifecycle

```text
Agency submits service
  -> Admin approves service
  -> Advertiser or publisher orders service
  -> Agency submits delivery URL
  -> Client or admin approves delivery
  -> Agency payout is released
```

Agency services cover work such as SEO writing, logo design, video production, video editing, creative strategy, landing pages, and ads management.
