# MCV Ads Control Plane

Laravel control plane for the MCV Network advertiser dashboard, account onboarding, wallet ledger, and MVP API surface.

## Requirements

- PHP 8.3+
- Composer 2
- Node.js 22.12.0, or any version matching `^20.19.0 || >=22.12.0`
- SQLite for local development
- Redis/PostgreSQL for production targets

Use the pinned Node version:

```bash
nvm use
```

## Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
```

Start the local app:

```bash
php artisan serve --host=127.0.0.1 --port=8010
```

The app is available at `http://127.0.0.1:8010`.

## Development Commands

Run tests:

```bash
composer test
```

Run PHP style checks:

```bash
composer lint
```

Build frontend assets:

```bash
npm run build
```

Run the full local development stack:

```bash
composer dev
```

This starts Laravel, the queue listener, logs, and Vite.

## Environment Notes

Local defaults intentionally use SQLite, database-backed queue/cache/session, and log mail for easy setup.

Production should override:

- `APP_URL=https://ads.mcv.network`
- `APP_ENV=production`
- `APP_DEBUG=false`
- `DB_CONNECTION=pgsql`
- `SESSION_DRIVER=redis`
- `QUEUE_CONNECTION=redis`
- `CACHE_STORE=redis`
- real mail credentials
- OAuth credentials stored as deployment secrets

## CI

The GitHub Actions workflow at `.github/workflows/control-plane-ci.yml` runs:

- Composer install
- npm install
- env/key/database bootstrap
- migrations
- Pint lint check
- Vite build
- PHPUnit tests

## Domain Split

- Marketing site: `mcv.network`
- Dashboard/API: `ads.mcv.network`
- SDK CDN: `cdn.mcv.network` or `cdn.softelads.com`
