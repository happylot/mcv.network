---
title: Runbook
---

# Operations Runbook

## Health Checks

Control plane:

```bash
curl -kfsS https://ads.mcv.network/up
```

Docs:

```bash
curl -fsS https://docs.mcv.network/
```

## Common Live Checks

Protected control-plane routes should redirect unauthenticated users to login:

```bash
curl -k -I https://ads.mcv.network/marketplace/websites
curl -k -I https://ads.mcv.network/admin/publisher-websites
```

## Laravel Cache

After remote changes:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## SQLite Lock

Current live environment uses SQLite. If a manual DB update hits `database is locked`, retry with a short busy timeout or wait for the active request to finish.
