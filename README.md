# Book Palace — Online Book Shop (PHP + Supabase PostgreSQL)

A PHP 8 book shop (customer storefront and admin dashboard) migrated from
MySQL/MariaDB to **Supabase PostgreSQL** with a private database schema and
row level security (RLS). The UI is unchanged; the data layer is now
PostgreSQL-only and server-rendered.

## Architecture

| Concern | Implementation |
| --- | --- |
| Database | Supabase free-tier PostgreSQL, private `bookshop` schema |
| Data access | Server-side PHP PDO (`pdo_pgsql`) with native prepared statements |
| Authentication | Existing PHP username/password sessions; `password_hash`/`password_verify` (bcrypt) |
| Authorization | RLS policies plus stored `role`; admin pages call `require_admin()` |
| Checkout | `bookshop.checkout(...)` derives the customer from the session, records the payment method (cash on delivery or demo MMQR), locks stock, snapshots server prices, and is atomic |
| Browser API | No Supabase Data API or Auth usage; no browser keys are shipped |

The `bookshop` schema is intentionally **not** exposed through PostgREST. PHP is
the only client and connects directly to PostgreSQL.

Application layout: `index.php` redirects to the public storefront in `User/`,
administrator pages live in `Admin/`, shared database/session code is in
`includes/`, and all database tooling is in `database/`, `supabase/` and `tests/`.

## Requirements

- PHP 8.2+ with `pdo_pgsql` (plus `fileinfo`, `openssl`)
- PostgreSQL client tools (`psql`) or the Supabase dashboard SQL editor
- Python 3 (only to regenerate the catalog seed)
- A Supabase project (free tier is enough) for production

## Database setup (Supabase)

1. Create a Supabase project.
2. Apply the SQL in `supabase/migrations/` once, in filename order
   (Dashboard → SQL Editor, or `supabase db push` with the Supabase CLI).
3. Load catalog data only: run `supabase/seed.sql`. It contains 56 authors,
   6 categories, 95 books and 95 details. No legacy customers, orders, comments
   or feedback are imported.
4. Create the restricted runtime login and give it a long random password:
   ```sql
   -- run as the project owner (postgres) once
   \password bookshop_app
   ```
   Or in the SQL editor: `ALTER ROLE bookshop_app PASSWORD '...';` (keep the
   password in a secret manager, never in Git).
   The same can be done from the CLI with the migration owner credentials in
   `ADMIN_DB_*`; `--write-env` also updates `DB_PASSWORD` in `.env`:
   ```text
   BOOKSHOP_DB_PASSWORD='...' php scripts/set-db-password.php --write-env
   ```
   Add `--dry-run` first to validate the `ADMIN_DB_*` configuration without
   changing anything. The new password is read from `BOOKSHOP_DB_PASSWORD` or a
   hidden prompt, never from an argument.
5. Copy `.env.example` to `.env` (untracked) and fill in the **session pooler**
   connection values:
   - Host/port: session pooler on **5432** (supports native prepares).
     The transaction pooler on 6543 is not compatible with this application.
   - User: `<runtime-role>.<project-ref>` (for example `bookshop_app.abcdefghijklmnop`).
   - `DB_SSLMODE=verify-full` and `DB_SSLROOTCERT` pointing to the downloaded
     Supabase CA certificate.
6. Keep the Data API schema list free of `bookshop`. Grants for `anon`,
   `authenticated` and `service_role` are revoked by the migration.
7. Create the first administrator (CLI only, reads `ADMIN_DB_*` and
   `BOOKSHOP_ADMIN_*`):
   ```text
   php scripts/bootstrap-admin.php
   ```


## Local development

```text
# 1. Ephemeral PostgreSQL (example using PostgreSQL 15 binaries)
initdb -D C:\temp\bookshop_pg -U postgres -A trust -E UTF8 --no-locale
pg_ctl -D C:\temp\bookshop_pg -o "-p 55439 -c listen_addresses=127.0.0.1" -l C:\temp\pg.log start
createdb -h 127.0.0.1 -p 55439 -U postgres bookshop_test

# 2. Schema + catalog
psql -h 127.0.0.1 -p 55439 -U postgres -d bookshop_test -X -v ON_ERROR_STOP=1 ^
  -f supabase/migrations/20261101222500_private_bookshop.sql
psql -h 127.0.0.1 -p 55439 -U postgres -d bookshop_test -X -v ON_ERROR_STOP=1 ^
  -f supabase/seed.sql

# 3. Runtime environment (local trust auth: no password, SSL disabled)
set APP_ENV=local
set DB_HOST=127.0.0.1
set DB_PORT=55439
set DB_NAME=bookshop_test
set DB_USER=bookshop_app
set DB_SSLMODE=disable

# 4. Serve through the hardened router
php -S 127.0.0.1:8099 scripts/dev-router.php
```

Apache/shared hosting uses `.htaccess` instead of the router; both block
`.env`, migrations, tests and tooling, and uploaded-file execution.

### Laragon on Windows (hosted Supabase)

Laragon already generated the virtual host, so the app runs at
`http://bookpalace.test` once two things are true:

1. **PHP has `pdo_pgsql`.** Laragon ships it disabled: uncomment
   `extension=pdo_pgsql` and `extension=pgsql` in
   `c:\laragon\bin\php\<version>\php.ini`, then restart Apache.
2. **`.env` holds the runtime login** — `APP_ENV=local`, the session-pooler host
   on port 5432, `DB_USER=bookshop_app.<project-ref>`, the `bookshop_app`
   password, and `DB_SSLROOTCERT` pointing at `.secrets/prod-ca-2021.crt`.
   `APP_ENV=local` matters: otherwise the session cookie is marked `Secure` and
   sign-in silently fails over plain HTTP.

XAMPP's PHP already ships `pdo_pgsql`, so the built-in server is a one-liner
from the project root when Laragon is not running:

```text
php -S 127.0.0.1:8099 scripts/dev-router.php
```

Apply a pending migration from the local CLI with the owner credentials in
`ADMIN_DB_*`; add `--dry-run` first to validate the configuration:

```text
php scripts/apply-migration.php supabase/migrations/<file>.sql --dry-run
php scripts/apply-migration.php supabase/migrations/<file>.sql
```

## Security model

- **RLS is always on** for all nine tables. Anonymous requests can read the
  catalog and insert feedback; customers can read only their own orders and
  manage only their own profile/comment rows; admin-only writes are gated by a
  stored role check inside `SECURITY DEFINER` helpers with `search_path = ''`.
- Each PHP database call sets `app.user_id` **transaction-local** from the
  validated server session before running one prepared statement, so pooled
  connections cannot leak another customer's identity.
- The runtime login (`bookshop_app`) is `NOSUPERUSER NOBYPASSRLS NOCREATEROLE
  NOCREATEDB` and is not the table owner. `users.password` is not selectable by
  the runtime role except through the single-purpose `login_record()` function.
- Passwords use `password_hash(PASSWORD_DEFAULT)`; legacy MD5 accounts are
  deliberately **not** migrated. Legacy personal data was removed from the
  tracked MySQL dump (Git history still contains the old file, so rotate any
  credential that ever appeared there).
- All POST forms use a session-bound CSRF token. Uploaded images are MIME and
  size validated and renamed. Errors are logged without SQL parameters or
  customer data.
- **Boundary:** the PHP process holds the database credential and is trusted to
  set its own session identity. RLS protects against application authorization
  mistakes, not a compromised PHP host. Never expose the credential to clients
  or allow arbitrary SQL from users.

## Validation

```text
php -l <file>                                  # lint
psql ... -U postgres -f tests/database_security.sql
```

`tests/database_security.sql` runs as the real runtime role and covers schema
ownership, RLS, grants, anonymous access, cross-user isolation, password
column denial, role escalation, malformed session IDs, checkout validation,
payment-method validation, server pricing, stock checks and rollback. It rolls
back all fixtures.

## Operational notes

- Rotate the runtime database password periodically and after any leak.
- Enable Supabase backups/point-in-time recovery appropriate to the plan.
- Order status changes set `senddate`; order deletion does not restore stock.
- MMQR checkout is a **demo**: `User/orderconfirm.php` opens the browser-generated
  QR (from `assets/vendor/qrcode/`) in a Bootstrap modal popup right after an MMQR
  order is placed, and its "I have paid" button only sets a session flag
  (`User/orderpay.php` now redirects to that popup to keep old links working).
  `orders.payment_method` stores `cod` or `mmqr`, but nothing marks an order as
  paid until a real provider (for example MyanMyanPay) and its verified webhook
  are wired in.
- `database/bookpalace.sql` is a **sanitized catalog-only** historical MySQL
  dump used by `scripts/convert_catalog.py`; never import it as-is.
