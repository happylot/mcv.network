---
title: Deployment
---

# Deployment

## Apps

| App | Path | Domain |
| --- | --- | --- |
| Marketing static site | repository root static files | `mcv.network` |
| Laravel control plane | `apps/control-plane` | `ads.mcv.network` |
| Docusaurus docs | `apps/docs` | `docs.mcv.network` |

## Control Plane Deploy

Workflow:

```text
.github/workflows/deploy-control-plane.yml
```

Deploy target:

```text
/var/www/mcv.network/current/apps/control-plane
```

## Docs Deploy

Workflow:

```text
.github/workflows/deploy-docs.yml
```

Suggested deploy target:

```text
/var/www/mcv.network/docs
```

Nginx root:

```text
/var/www/mcv.network/docs
```
