# VYORA — Intelligent Commerce

> *Discover differently.* A Laravel storefront with **VYORA MATCH** (a transparent, rule-based product matcher), a server-priced cart and checkout, Stripe Checkout with a clearly-labelled demo mode, order tracking, and an admin dashboard.

## 0. Read this first — what has and hasn't been verified

This code was written in a sandbox that could not reach Packagist or the npm registry, so **Laravel itself was never installed there**. As a result:

| Verified in the sandbox | NOT verified (run these yourself) |
|---|---|
| Every PHP file passes `php -l` (syntax). | `php artisan migrate --seed` |
| Blade directive pairs balance; every `route()` name, view, `@include`, `<x-component>` and `<livewire:…>` referenced exists. | `php artisan test` (the 7 test files in `tests/`) |
| The generated SVG artwork is well-formed. | `npm run build` (Vite/Tailwind v4) |
| | Livewire/Alpine behaviour in a browser, Stripe end-to-end, mail, Google sign-in |
| | Vercel/any host deployment |

Expect to fix a handful of small runtime issues on first run (typos, a missing import, a Blade expression). Run the tests first — they cover the money, stock, webhook and authorization paths and will point at problems quickly. Nothing here claims a deployment works; see §14.

## 1. Project overview
Storefront: home (with registration section), shop with live filters/sorting, categories, search overlay + results, product pages (variants, gallery, reviews), cart drawer + page, multi-step checkout, order confirmation + tracking timeline, account area (orders, wishlist, addresses, preferences), deals, trending, new arrivals, editorial collections, VYORA MATCH, static pages. Admin: dashboard, products (variants, images, tags, collections), inventory with movement log, orders (fulfilment + tracking), customers/roles, reviews moderation, coupons, promotions, categories/brands/collections, newsletter, store settings.

Integrity rules baked in: prices/discounts/tax/shipping are computed **server-side in integer cents**; stock is reserved under row locks; an idempotency key prevents duplicate orders; an order is only "paid" after Stripe's signed webhook (or an explicit demo confirmation); reviews require a paid + shipped purchase and are moderated; there is no fake urgency, reviews or activity. Seeded reviews/metrics are flagged `is_demo` and the storefront shows a demo-data notice while the `demo_data` setting is on.

## 2. Technology stack
Laravel 11/12 · PHP 8.2+ · Blade · Livewire 3 · Alpine.js (bundled with Livewire) · Tailwind CSS v4 · Vite · MySQL or PostgreSQL (SQLite works for quick tries) · Eloquent · Stripe PHP SDK · Chart.js (admin) · PHPUnit-style feature tests.

## 3. Prerequisites
PHP 8.2+ with `mbstring, xml, curl, pdo_*`, Composer 2, Node 20+, a database server.

## 4. Installation
This repo is an **overlay** for a fresh Laravel app (it overwrites `routes/web.php`, `routes/console.php`, `bootstrap/app.php`, `package.json`, `vite.config.js`, `resources/css/app.css`, `resources/js/app.js`).
```bash
./install.sh ~/code/vyora          # creates the Laravel app if absent, copies files, installs livewire + stripe-php
cd ~/code/vyora
```
Manual equivalent: `composer create-project laravel/laravel vyora`, copy this folder's contents over it, then `composer require livewire/livewire stripe/stripe-php`. Do **not** install a starter kit (Breeze/Jetstream) — VYORA ships its own auth.

## 5. Environment configuration
`.env.example` documents every key. Set `APP_URL`, DB credentials, and (optionally) Stripe/Google keys. Key VYORA settings live in `config/vyora.php` (tax rate, free-shipping threshold, shipping methods, max cart quantity, low-stock threshold).

## 6. Database setup
Create an empty database (`CREATE DATABASE vyora CHARACTER SET utf8mb4;`) and put credentials in `.env`.

## 7. Migrations and seeding
```bash
php artisan migrate --seed
```
Seeds 8 categories, 48 demo products (fictional brands, variants, tags, attributes, locally generated artwork), the 8 editorial collections, two running promotions with real start/end dates, coupons `WELCOME10` and `FREESHIP`, and flagged demo reviews. All seeders are idempotent.

## 8. Authentication setup
Email/password registration, login, logout, password reset (tokens + throttling), optional email verification (`VYORA_REQUIRE_EMAIL_VERIFICATION=true`). Guest cart and wishlist merge on login/registration. **Google sign-in** is optional: `composer require laravel/socialite`, set the three `GOOGLE_*` keys; the button stays hidden otherwise.

## 9. Stripe integration
Set `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`. Checkout creates a Stripe **Checkout Session** from server-computed totals. Local webhook testing: `stripe listen --forward-to localhost:8000/stripe/webhook` and copy the printed signing secret. Handled events: `checkout.session.completed` (only when `payment_status=paid`), `async_payment_succeeded/failed`, `expired`. Events are signature-verified and stored once (`stripe_events`) so retries are safe. Refunds are **not** automated — issue them in the Stripe dashboard.

## 10. Demo checkout mode
With `VYORA_DEMO_CHECKOUT=true` and no Stripe key, checkout routes to a page that is explicit that **no card is charged**; confirming records a *demo* payment and the order is flagged `is_demo` everywhere (emails, admin, account). Defaults to on outside production and off in production. Demo orders are excluded from admin revenue.

## 11. Local development
```bash
composer run dev        # server + queue + vite (if the stock script exists)  — or:
php artisan serve & npm run dev
php artisan schedule:work   # releases abandoned unpaid orders every 10 min (vyora:release-stale-orders)
```

## 12. Testing
```bash
php artisan test
```
Covers: registration/login/protected routes, role mass-assignment protection, cart/coupon/promotion math, stock clamping, checkout (totals, oversell, idempotency, restock-once), demo payment, Stripe webhook (bad signature, duplicates, unpaid, expired), order and address ownership, review eligibility + moderation, search escaping, filters/sort, wishlist persistence, admin authorization, page rendering.

## 13. Production asset build
`npm ci && npm run build` then `php artisan config:cache route:cache view:cache event:cache`.

## 14. Deployment instructions
Not tested by the author. What was checked (Oct 2026): Vercel's own knowledge-base guide *"Laravel PHP with Docker"* runs Laravel on Vercel Functions in a **custom container (FrankenPHP)**; it is not described as a first-class PHP runtime, and it states: the filesystem is not durable (use object storage for uploads), use external session/cache stores, queue workers and the scheduler don't fit (use Vercel Cron/Queues), run migrations as a separate release step. VYORA relies on a database, the `public` disk for uploads, a scheduler and (optionally) queues, so **a long-running host is the lower-risk choice** (Laravel Cloud, Forge + a VPS, Fly.io, or any Docker host). Whichever you pick:
1. `APP_ENV=production`, `APP_DEBUG=false`, strong `APP_KEY`, HTTPS, `VYORA_DEMO_CHECKOUT=false`.
2. Managed MySQL/Postgres; `php artisan migrate --force` as a release step. Do **not** run the demo seeder on a live store (run only the settings/admin pieces you need).
3. Persistent or object storage for `public` uploads (`FILESYSTEM_DISK=s3` etc.) and `php artisan storage:link` where the disk is local.
4. Real Stripe live keys + a webhook endpoint at `https://YOUR-DOMAIN/stripe/webhook`.
5. A cron entry: `* * * * * php artisan schedule:run`.
6. Real SMTP (`MAIL_MAILER`), and review the **draft** Privacy/Terms pages with a lawyer before launch.
Source for the Vercel notes: https://vercel.com/kb/guide/laravel-php-with-docker

## 15. Admin account setup
No credentials are hard-coded. Create one locally (password is prompted, never stored in the repo):
```bash
php artisan vyora:create-admin you@example.com --name="Your Name"
# staff (can't edit settings or roles):  --role=staff
```
Admins can then promote others under Admin → Customers.

## 16. Storage configuration
Product uploads go to the `public` disk (`php artisan storage:link`). Uploads are validated (JPG/PNG/WebP, ≤4 MB) and stored under random names. Products with no uploaded image use locally generated SVG artwork (`/art`), which is illustrative — replace with real photography via Admin → Products.

## 17. Troubleshooting
- **Blank styles / 500 on `@vite`** → run `npm run build` or `npm run dev`.
- **"Class Livewire…/Stripe… not found"** → `composer require livewire/livewire stripe/stripe-php`.
- **Sessions/cache table errors** → run migrations, or set `SESSION_DRIVER=file CACHE_STORE=file`.
- **Webhook 503** → `STRIPE_WEBHOOK_SECRET` is empty. **Webhook 400** → wrong secret or body modified by a proxy.
- **Images 404** → `php artisan storage:link`.
- **Demo checkout not available** → check `VYORA_DEMO_CHECKOUT` and that `APP_ENV` isn't `production`.
- **Lazy-loading warnings in the log** → `Model::preventLazyLoading` is log-only; they flag N+1 queries to tidy.

## 18. Known limitations
- Unexecuted code (see §0). Treat the first run as integration testing.
- No automated refunds, partial fulfilment, returns workflow or shipping-carrier rate lookups; shipping is flat-rate per method and tax is a flat configured rate (not a tax engine).
- VYORA MATCH is deterministic rule-based scoring over tags/price/category, **not** an AI model (the UI says so).
- Product artwork is generated illustration, not photography.
- Privacy/Terms are drafts, not legal advice.
- Single currency (USD), single language.
- Email is sent synchronously unless you configure a queue.
