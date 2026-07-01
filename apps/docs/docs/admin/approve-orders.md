---
title: Approve Orders
---

# Approve Guest Post Orders

Admins review order fulfillment from `/admin/orders`.

## When Admin Approval Is Used

Admin approval is useful when:

- Advertiser is unavailable.
- Advertiser asks support to verify fulfillment.
- There is a dispute or manual review.
- Marketplace ops needs to release payout after checking the published URL.

## Approval Requirements

Only submitted orders can be approved.

Required order state:

```text
submitted
```

## Result

Approval changes the order to:

```text
completed
```

The publisher wallet receives a posted ledger credit:

```text
type = guest_post_payout
direction = credit
```
