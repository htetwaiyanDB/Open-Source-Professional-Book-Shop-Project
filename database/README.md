# Private PostgreSQL data model

`bookpalace.sql` is the **sanitized historical MySQL catalog input**, not an
install script. It now contains only author, categorys, products and
product_details rows: all legacy users, MD5 hashes, personal contacts, orders,
orderdetails, comments and feedback were removed. Git history still has the
original file, so rotate any credential that ever appeared there. Never import
that dump as-is into PostgreSQL.

`schema.sql` is the canonical schema source, copied byte-for-byte to
`supabase/migrations/20261101222500_private_bookshop.sql`. Apply that migration
once through the Supabase CLI (`supabase db push`) or the dashboard SQL editor
as the project owner (`postgres`), then run `supabase/seed.sql` if catalog data
is wanted. Do not apply both the source and the migration to one database.

The migration must run as the Supabase `postgres` role, which has `CREATEROLE`
but is **not** a superuser. It therefore creates `bookshop_app` with safe
attributes and verifies them instead of issuing superuser-only `ALTER ROLE
... NOSUPERUSER/NOREPLICATION/NOBYPASSRLS` statements. If a pre-existing
`bookshop_app` role has unsafe attributes, migration stops with `42501` and a
Supabase administrator must correct it first.

## Trust and deployment boundary

- `bookshop` must **not** be in the project's exposed Data API schemas. The
  schema is for direct server-side PHP PDO PostgreSQL connections, not REST or
  Supabase Auth. Grants are explicitly revoked from `PUBLIC`, `anon`,
  `authenticated` and `service_role` when present.
- Every table/function is owned by `postgres`. `bookshop_app` is a restricted
  `LOGIN NOSUPERUSER NOBYPASSRLS NOCREATEDB NOCREATEROLE NOREPLICATION NOINHERIT`
  role without memberships. The migration does not embed a password; provision
  a long random runtime password out of band. Never use the migration owner as
  the application's database login.
- PHP validates its server-side session's positive numeric user ID, begins a
  transaction, sets `app.user_id` using `set_config('app.user_id', :id, true)`,
  executes bound SQL, and commits/rolls back. Anonymous requests set `''`.
  `true` means transaction-local, preventing identity leakage on connection
  reuse. Do not use a browser parameter, posted role, JWT or cookie directly.
- **The runtime credential can set this custom setting itself.** RLS protects
  against application authorization mistakes, not a compromised PHP process or
  arbitrary SQL execution with that credential. Use bound parameters everywhere,
  never expose database credentials, and never give untrusted clients raw SQL.
- `current_user_id()` rejects malformed IDs/overflow to NULL. `is_admin()` reads
  the actual server-owned users role through an owner-executed, pinned-empty-
  search-path function, avoiding recursive users policies. Tables intentionally
  are not `FORCE ROW LEVEL SECURITY` so audited definer functions can work.

## SQL API consumed by PHP

All function names are in `bookshop`, all definer functions have `search_path=''`
and are executable only by `bookshop_app` (plus their owner):

| Function | Result / permission |
| --- | --- |
| `current_user_id()` | Valid setting ID or NULL; does not assert existence |
| `is_admin()` | Existing DB user's persisted `admin` role |
| `login_record(p_username text)` | Zero/one row `(userid bigint, username text, password text, role text)`; PHP uses `password_verify` |
| `register_user(p_username text,p_hash text,p_email text,p_phone text,p_address text)` | New bigint ID, accepts PHP bcrypt hash and **forces user role** |
| `admin_create_user(same five parameters,p_role text)` | New bigint ID, requires current DB administrator |
| `admin_delete_user(p_userid bigint)` | Void; requires admin, cannot delete self, orders FK prevents loss of history |
| `checkout(p_cart jsonb,p_deliveryname text,p_email text,p_deliveryphone text,p_address text)` | New bigint order ID, requires an existing current DB user |

Users direct SELECT is column-limited to `userid,username,email,phone,address,role`
and row-limited to self/admin. **`SELECT *` and direct `password` SELECT fail even
for an administrator using the runtime login.** Only `login_record` returns the
hash for one username, explicitly for the trusted PHP authentication code.
Rate-limit the login/registration endpoints. Direct updates may modify only
`username,email,phone,address`; neither role nor password is writable. First
administrator provisioning is an operator-only insert using an independently
generated bcrypt hash; there are no default credentials or legacy accounts.

Checkout accepts an array such as `[{"productid":1,"qty":2}]`, 1–100 unique lines,
integer JSON numbers only, quantity 1–999, no extra keys (especially prices).
It derives customer ID from the trusted setting; validates delivery fields and
the DB user's existence; locks inventory in ascending product ID order; rejects
missing/insufficient inventory; snapshots server prices; writes orders/details
and decrements stock atomically. `orderdetails.amount` is line total and
`unit_price` is the price snapshot. Rollback reverts every write. No direct
runtime insert on orders/details or update on details exists. Admins may update
only order `status` (0/1) and `senddate` or delete orders, which cascades details;
deletion does not imply an inventory refund. A cancellation/refund feature
requires its own reviewed stock-accounting function.

Catalog is publicly readable through the PHP runtime, admin-write only. Comment
content is public; insert requires logged-in matching `userid`; edits/deletion
are admin-only. Feedback insert allows anonymous `name,email,phone,message`;
read/delete is admin-only. Do not request `RETURNING` feedback data on anonymous
insert. Web validation, anti-spam/rate limits, escaping and CSRF remain PHP duties.

## Catalog conversion and tests

Run `python scripts/convert_catalog.py` to regenerate `supabase/seed.sql`.
The allowlist parser preserves Unicode and MySQL quoted-string escapes and
copies only author/categorys/products/product_details. It validates IDs, foreign
keys, nonnegative stock/prices/pages. The historical orphan detail ID 96 points
to absent product 96; the converter explicitly omits it instead of inventing
a sellable item with unknown price or inventory. Output counts: 56 authors,
6 categories, 95 products, 95 details. Identity sequences are advanced past seeds.

After applying the migration and seed, run as a local test database owner:

```text
psql -X -v ON_ERROR_STOP=1 -f tests/database_security.sql <connection options>
```

Tests run as the real runtime role, assert schema/role/definer attributes,
anonymous and cross-user access, forbidden password access and role escalation,
admin management, malformed identity contexts, cart validation, spoofed prices,
stock checks and checkout/outer-transaction rollback. Fixtures are wrapped in a
rollback. Never point fixture/security tests at a live production database.