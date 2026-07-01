---
title: Submit Website
---

# Submit Website

Publishers add websites from `/publisher/websites/create`.

## Required Website Data

| Field | Notes |
| --- | --- |
| Domain | Root domain; paths are normalized away |
| Website name | Display name |
| Niche | Marketplace category |
| Language | Example: `en` |
| Country | Two-letter country code |
| Monthly traffic | Claimed traffic metric |
| DR | Domain Rating, 0-100 |
| DA | Domain Authority, 0-100 |
| Guest post price | USD price |
| Turnaround days | Expected fulfillment time |
| Sample URL | Optional sample post |
| Guidelines | Editorial rules for advertisers |

## Status

New websites start as:

```text
pending_review
```

Only `approved` websites are visible to advertisers in the marketplace.
