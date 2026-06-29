# MCV Ads Platform — Laravel MVP Roadmap

## 1. Product Goal

Build the first usable advertiser control plane for MCV Network:

- Advertisers can register, log in, verify email, and manage their account.
- Advertisers can top up balance and see ledger transactions.
- Admin can approve accounts, view balances, and manually reconcile payments.
- The platform exposes stable API boundaries for future JS SDK and ad delivery.

Laravel owns the control plane and MVP API. The JS SDK and ad delivery contract are designed as separate surfaces from day one.

---

## 2. MVP Stack

| Layer | Choice | Notes |
|------|--------|-------|
| Backend | Laravel 11/12 | Control plane, MVP API, billing ledger |
| Admin | Filament | Fast internal admin for users, accounts, payments |
| Auth | Laravel Breeze or Jetstream + Sanctum | Start simple; Sanctum for dashboard/API auth |
| Database | PostgreSQL preferred, MySQL acceptable | Accounts, campaigns, ledger, payments |
| Cache/Queue | Redis | Sessions, queue, rate limits, future frequency caps |
| Payments | Stripe first, manual bank transfer fallback | Use webhook-confirmed crediting only |
| Storage | S3-compatible or local for dev | Receipts, future creative uploads |
| SDK Build | TypeScript + esbuild/Rollup | Separate package/release pipeline |
| Deployment | Nginx + PHP-FPM + Supervisor/Horizon | No Kubernetes for MVP |

---

## 3. Architecture Boundaries

### Laravel Control Plane

Laravel handles:

- Auth, email verification, password reset.
- Advertiser and publisher account records.
- Wallet/balance ledger.
- Payment intent creation and webhook reconciliation.
- Campaign/creative/publisher management in later milestones.
- Admin operations via Filament.

### MVP API

Laravel initially exposes:

- `POST /api/v1/config`
- `POST /api/v1/ad`
- `POST /api/v1/event`

These APIs must be treated as replaceable delivery boundaries. The SDK should only depend on HTTP contracts, never on Laravel sessions, Blade views, or app internals.

### JS SDK

The SDK is a separate TypeScript package:

- CDN-hosted loader.
- Versioned release path, for example `/sdk/v1/softel-ads.min.js`.
- Talks to public API endpoints only.
- Can be released independently from the Laravel dashboard.

---

## 4. Implementation Phases

### Phase 0 — Project Foundation

Target: 0.5-1 day

Status: Closed locally.

Verification:

- Laravel app exists in `apps/control-plane`.
- Local Git repository initialized.
- Root and Laravel `.gitignore` files protect env/dependency/build artifacts.
- Control plane README documents local setup and commands.
- Laravel deployment notes exist in `docs/CONTROL_PLANE_DEPLOYMENT.md`.
- CI workflow exists at `.github/workflows/control-plane-ci.yml`.
- `php artisan migrate:status` shows all current migrations ran.
- `composer lint`, `composer test`, and `npm run build` pass with the pinned Node runtime.

- Create Laravel app under a new backend directory, for example `apps/control-plane`.
- Configure env files, database, Redis, mail, queue.
- Add base CI: lint, tests, migrations.
- Add `.gitignore`, Git repo, and deployment notes.
- Decide primary domain split:
  - Marketing site: `mcv.network`
  - Dashboard/API: `ads.mcv.network`
  - SDK CDN: `cdn.mcv.network` or `cdn.softelads.com`

### Phase 1 — Auth + Account + Wallet

Target: 1-2 days

- Register/login/logout.
- Email verification and password reset.
- Create advertiser account on signup.
- Add account status: `pending`, `active`, `suspended`.
- Add wallet table and immutable ledger entries.
- Add top-up flow:
  - Stripe card payment.
  - Manual bank transfer request.
  - Webhook-confirmed wallet credit.
- Add basic dashboard:
  - Balance.
  - Add funds.
  - Transaction history.
  - Account profile.

### Phase 2 — Admin Console

Target: 1-2 days

- Filament admin login.
- Manage users/accounts.
- View top-up requests and payment ledger.
- Manually mark bank transfer as paid.
- Suspend/reactivate accounts.
- Audit log for balance-affecting actions.

### Phase 3 — Campaign Skeleton

Target: 2-4 days

- Campaign CRUD.
- Budget, daily cap, status.
- Placement and format selection.
- Creative upload/approval.
- Campaign status workflow: draft, pending review, active, paused, rejected.

### Phase 4 — MVP Ad Delivery

Target: 3-5 days

- `/api/v1/config`: site/placement config.
- `/api/v1/ad`: return eligible creative based on simple rules.
- `/api/v1/event`: ingest impression/click events.
- Queue event processing.
- Daily aggregate report by campaign.
- Basic anti-abuse: rate limits, signed placement IDs, duplicate event protection.

### Phase 5 — Web SDK Alpha

Target: 3-5 days

- TypeScript loader.
- Native widget renderer.
- Impression/click tracking.
- Consent-aware first-party ID.
- CDN release workflow.
- Test page and publisher install snippet.

---

## 5. Today Scope — Register, Login, Top-Up

Goal for today: a working Laravel dashboard where an advertiser can create an account, log in, top up balance, and see the transaction history.

### 5.1 Decisions To Lock

- Use Laravel Breeze unless the product needs teams/2FA immediately.
- Use Sanctum for API tokens/session auth.
- Use Stripe Checkout or Payment Intents for card top-up.
- Support manual bank transfer as a fallback payment method.
- Credit wallet only after trusted confirmation:
  - Stripe webhook `payment_intent.succeeded` or `checkout.session.completed`.
  - Admin approval for bank transfer.

### 5.2 Database Tables

#### `users`

Use Laravel default fields plus:

- `name`
- `email`
- `password`
- `email_verified_at`

#### `accounts`

| Field | Type | Notes |
|------|------|-------|
| `id` | uuid/bigint | Primary key |
| `owner_user_id` | foreign id | Main user |
| `type` | string | `advertiser`, `publisher`, `agency`, `admin` |
| `name` | string | Company/account name |
| `status` | string | `pending`, `active`, `suspended` |
| `currency` | char(3) | Default `USD` |

#### `account_user`

For future team members:

| Field | Type |
|------|------|
| `account_id` | foreign id |
| `user_id` | foreign id |
| `role` | string: `owner`, `admin`, `member`, `billing` |

#### `wallets`

| Field | Type | Notes |
|------|------|-------|
| `account_id` | foreign id | One wallet per account |
| `currency` | char(3) | `USD` |
| `available_balance_cents` | bigint | Spendable balance |
| `pending_balance_cents` | bigint | Pending/review amount |

#### `wallet_ledger_entries`

Immutable source of truth.

| Field | Type | Notes |
|------|------|-------|
| `wallet_id` | foreign id | Wallet |
| `type` | string | `topup`, `spend`, `refund`, `adjustment` |
| `direction` | string | `credit` or `debit` |
| `amount_cents` | bigint | Positive integer |
| `currency` | char(3) | `USD` |
| `status` | string | `pending`, `posted`, `voided` |
| `reference_type` | string nullable | e.g. `payment` |
| `reference_id` | string nullable | External/internal reference |
| `metadata` | json nullable | Gateway details |

#### `payments`

| Field | Type | Notes |
|------|------|-------|
| `account_id` | foreign id | Advertiser account |
| `provider` | string | `stripe`, `bank_transfer`, `manual` |
| `provider_reference` | string nullable | Stripe session/payment id |
| `amount_cents` | bigint | Requested amount |
| `currency` | char(3) | `USD` |
| `status` | string | `requires_payment`, `pending`, `succeeded`, `failed`, `cancelled` |
| `metadata` | json nullable | Raw provider details |

### 5.3 Routes

#### Web Routes

| Method | Path | Purpose |
|-------|------|---------|
| `GET` | `/register` | Signup form |
| `POST` | `/register` | Create user + account + wallet |
| `GET` | `/login` | Login form |
| `POST` | `/login` | Login |
| `POST` | `/logout` | Logout |
| `GET` | `/dashboard` | Balance and quick actions |
| `GET` | `/billing` | Wallet + transaction history |
| `GET` | `/billing/top-up` | Top-up form |
| `POST` | `/billing/top-up` | Create payment |
| `GET` | `/billing/success` | Payment return page |
| `GET` | `/billing/cancel` | Payment cancelled page |

#### API/Webhook Routes

| Method | Path | Purpose |
|-------|------|---------|
| `POST` | `/webhooks/stripe` | Confirm Stripe payments |
| `GET` | `/api/v1/me` | Current user/account |
| `GET` | `/api/v1/billing/ledger` | Transaction history |

### 5.4 Services

#### `AccountOnboardingService`

Responsible for:

- Creating account after registration.
- Attaching owner role.
- Creating wallet.
- Setting account status.

#### `WalletService`

Responsible for:

- Posting immutable ledger entries.
- Updating cached wallet balance inside a DB transaction.
- Rejecting negative top-up amounts.
- Preventing duplicate credits using payment reference id.

#### `PaymentService`

Responsible for:

- Creating Stripe Checkout/Payment Intent.
- Creating manual bank transfer payment records.
- Handling payment state transitions.
- Calling `WalletService` only after trusted confirmation.

### 5.5 Acceptance Criteria For Today

- A new advertiser can register with name, email, password, account type.
- User can log in and reach `/dashboard`.
- Account and wallet are created automatically.
- Dashboard shows current balance.
- User can start a top-up for at least `$100`.
- Payment record is created with correct status.
- On Stripe webhook success, wallet balance is credited exactly once.
- Billing page shows transaction history.
- Admin/manual flow can mark a bank transfer as paid and credit wallet.
- All balance changes have ledger entries.
- Basic tests cover register, login, create payment, webhook credit, duplicate webhook protection.

### 5.6 Suggested Build Order Today

1. Scaffold Laravel app and auth.
2. Add account/wallet/payment migrations.
3. Implement account creation on registration.
4. Build dashboard and billing pages.
5. Implement top-up creation.
6. Implement wallet ledger posting.
7. Add Stripe webhook or manual payment confirmation.
8. Add tests for money-critical flows.

---

## 6. Money Handling Rules

- Store all money as integer cents.
- Never update wallet balance without a ledger entry.
- Never credit from client return URL alone.
- Webhooks must be idempotent.
- Top-up minimum starts at `$100`.
- Payment provider fees should be tracked separately later, not silently deducted from ledger entries.
- Admin adjustments require reason, actor id, timestamp, and audit log.

---

## 7. Definition Of Done For Auth + Top-Up

- User flow works end-to-end locally.
- Failed payment does not credit wallet.
- Refreshing success page does not double credit.
- Replayed webhook does not double credit.
- Ledger sum matches wallet balance.
- Sensitive routes require authentication.
- Payment webhook validates provider signature.
- Dashboard can be deployed independently from the static marketing site.
