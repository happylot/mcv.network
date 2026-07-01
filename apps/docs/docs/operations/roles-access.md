---
title: Roles & Access
---

# Roles & Access

Access is currently based on the current account type.

## Account Types

| Type | Access |
| --- | --- |
| `advertiser` | Marketplace buying and billing |
| `publisher` | Website submissions and order fulfillment |
| `agency` | Service catalog publishing and service order fulfillment |
| `admin` | Review queues and marketplace operations |

## Live Role Updates

If manual live correction is needed, update:

- `accounts.type`
- `account_user.role`

The current app expects account owners to have:

```text
account_user.role = owner
```

## Important Note

The app currently uses `currentAccount()` from the first owned account or first pivot account. If a user has multiple accounts, make sure the intended account is returned first or update the primary account directly.
