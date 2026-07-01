---
title: Fulfill Order
---

# Fulfill Order

Publishers fulfill orders from `/publisher/orders`.

## Publisher Responsibilities

For each order:

1. Review the advertiser target URL and requirements.
2. Publish the article on the approved website.
3. Submit the live article URL.
4. Add optional notes for the advertiser.

## Status Flow

```text
pending_publisher -> submitted -> completed
```

## Payout Release

When the order becomes `completed`, the publisher wallet receives a `guest_post_payout` ledger credit.

The payout credit is idempotent, so approving the same order twice does not double-credit the publisher.
