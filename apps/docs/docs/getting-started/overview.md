---
title: Overview
description: High-level guide to the MCV Network product surfaces.
---

# MCV Network Documentation

This documentation is the source of truth for the current product workflow across:

- `mcv.network`: marketing site, SEO pages, blog, and landing pages.
- `ads.mcv.network`: Laravel portal for advertisers, publishers, and admins.
- `docs.mcv.network`: product and operations documentation.

## Current Product Scope

The portal currently supports:

- Advertiser registration and login.
- Google OAuth login.
- Stripe Checkout wallet top-up.
- Publisher website submission.
- Admin website approval.
- Guest post marketplace browsing.
- Guest post purchase using wallet balance.
- Publisher fulfillment by submitting a published URL.
- Advertiser or admin approval to release publisher payout.

## Role Model

Role behavior is based on the current account `type`.

| Account type | Main dashboard | Primary jobs |
| --- | --- | --- |
| `advertiser` | Advertiser dashboard | Top up wallet, buy guest posts, approve fulfillments |
| `agency` | Advertiser dashboard | Same buying flow as advertiser |
| `publisher` | Publisher dashboard | Submit websites, fulfill orders, receive payout |
| `admin` | Admin dashboard | Approve websites, approve order fulfillment, operate marketplace |

## Documentation Workflow

Update these docs in the same pull request or commit as product changes.

Recommended rule:

> If a feature changes a user-visible workflow, update the matching docs page before deployment.
