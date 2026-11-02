# Deployment (demo)

Production demo host: `demoecom.winterarc.asia` on `47.82.249.130`.

## Server layout

| Item | Value |
| --- | --- |
| Application root | `/var/www/demoecom` (owned `www-data:www-data`) |
| Runtime secrets | `/var/www/demoecom/.env`, mode `640`, owned `root:www-data` |
| Web server | Nginx 1.22 (`/etc/nginx/sites-available/demoecom.winterarc.asia`) |
| PHP runtime | PHP-FPM 8.2, socket `/run/php/php8.2-fpm.sock` |
| FPM pool limits | `pm = ondemand`, `pm.max_children = 5`, `memory_limit = 128M` |
| Deploy helper | `/usr/local/bin/bookshop-deploy` (root, invoked through sudo) |
| Deploy account | `bookshop-deploy` (member of `www-data`, no general sudo) |

The VPS has 2 vCPU and ~700 MB RAM shared with other sites, so the PHP pool is
deliberately small. Uploaded book covers live in `Photos/` and are preserved
across deployments.

## Runtime configuration

`.env` is **not** in Git. It contains:

```text
APP_ENV=production
DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com
DB_PORT=5432                      # session pooler, required by the app
DB_NAME=postgres
DB_USER=bookshop_app.<project-ref>
DB_PASSWORD=…                     # restricted role, NOT the project owner
DB_SSLMODE=verify-full
DB_SSLROOTCERT=/etc/ssl/certs/supabase-pooler-ca.crt
```

The application refuses to start unless it authenticates as `bookshop_app`
(non-superuser, no `BYPASSRLS`), so the Supabase project-owner login is used only
for migrations and for `scripts/bootstrap-admin.php` through the `ADMIN_DB_*`
variables.

The transaction pooler on port `6543` is **not** supported: `includes/database.php`
disables emulated prepares, and transaction pooling breaks native prepared
statements plus the transaction-local `app.user_id` identity.

## Continuous deployment

`.github/workflows/deploy.yml` runs on every push to `main`:

1. lints all PHP files;
2. packages the repository (without `.git`/`.env`);
3. uploads the archive to `/tmp/bookshop-<sha>.tar.gz` as `bookshop-deploy`;
4. runs `sudo -n /usr/local/bin/bookshop-deploy <archive>`, which stages the
   release, keeps `.env` and `Photos/`, fixes ownership, and reloads PHP-FPM;
5. smoke-tests `/User/home.php` from the origin.

Required repository secrets:

| Secret | Value |
| --- | --- |
| `DEPLOY_HOST` | `47.82.249.130` |
| `DEPLOY_USER` | `bookshop-deploy` |
| `DEPLOY_SSH_KEY` | private ed25519 key whose public part is in `/home/bookshop-deploy/.ssh/authorized_keys` |
| `DEPLOY_PORT` | `22` |

## TLS

`certbot.timer` is enabled, so renewals are automatic. The certificate can only
be issued after `demoecom.winterarc.asia` resolves to `47.82.249.130` (Cloudflare
zone `winterarc.asia`). Once the record exists, run:

```text
/usr/local/bin/bookshop-enable-tls
```

That script waits for DNS, requests the certificate with the Nginx installer
(which adds the HTTPS server block and the HTTP→HTTPS redirect), and verifies the
resulting endpoint.

## Operations

```text
systemctl reload php8.2-fpm          # after a manual file change
nginx -t && systemctl reload nginx   # after editing the vhost
tail -f /var/log/nginx/demoecom.error.log
certbot renew --dry-run
```

To rotate the runtime database password, run the operator script as root on the
server (it reads the migration owner credentials from `ADMIN_DB_*`, rewrites
`DB_PASSWORD` in `/var/www/demoecom/.env`, and verifies the new login):

```text
cd /var/www/demoecom
read -rsp 'Supabase owner password: ' ADMIN_DB_PASSWORD; echo
read -rsp 'New bookshop_app password: ' BOOKSHOP_DB_PASSWORD; echo
export ADMIN_DB_PASSWORD BOOKSHOP_DB_PASSWORD
ADMIN_DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com ADMIN_DB_PORT=5432 \
  ADMIN_DB_NAME=postgres ADMIN_DB_USER=postgres.<project-ref> \
  ADMIN_DB_SSLMODE=verify-full \
  ADMIN_DB_SSLROOTCERT=/etc/ssl/certs/supabase-pooler-ca.crt \
  php8.2 scripts/set-db-password.php --write-env
```

`.env` is re-read on every request, so no reload is needed; a
`systemctl reload php8.2-fpm` afterwards is harmless. Verify with:

```text
curl -s -o /dev/null -w '%{http_code}\n' https://demoecom.winterarc.asia/User/home.php
```

## Schema migrations

`database/schema.sql` is the full canonical schema; every change after the first
revision ships as its own file in `supabase/migrations/` and is applied once.
Use the Supabase SQL editor as `postgres`, or the operator script below, which
takes the same `ADMIN_DB_*` credentials as the password rotation (the runtime
login `bookshop_app` has no DDL rights):

```text
cd /var/www/demoecom
read -rsp 'Supabase owner password: ' ADMIN_DB_PASSWORD; echo
export ADMIN_DB_PASSWORD
ADMIN_DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com ADMIN_DB_PORT=5432 \
  ADMIN_DB_NAME=postgres ADMIN_DB_USER=postgres.<project-ref> \
  ADMIN_DB_SSLMODE=verify-full \
  ADMIN_DB_SSLROOTCERT=/etc/ssl/certs/supabase-pooler-ca.crt \
  php8.2 scripts/apply-migration.php supabase/migrations/20261102160000_order_payment_method.sql
```

That migration adds `orders.payment_method` (`cod`/`mmqr`) and replaces
`bookshop.checkout()` with a six-argument version whose last argument defaults to
`cod`. Checkout keeps working before it is applied: `User/submit.php` falls back
to the five-argument signature on `42883` and keeps the chosen method in the
session only, so apply the migration before relying on the admin Payment column.
