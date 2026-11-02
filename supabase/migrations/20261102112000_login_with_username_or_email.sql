BEGIN;

CREATE UNIQUE INDEX IF NOT EXISTS users_email_ci_nonempty
  ON bookshop.users (lower(email))
  WHERE btrim(email) <> '';

CREATE OR REPLACE FUNCTION bookshop.login_record(p_username text)
RETURNS TABLE(userid bigint, username text, password text, role text)
LANGUAGE sql STABLE SECURITY DEFINER SET search_path = '' AS $fn$
  SELECT u.userid, u.username, u.password, u.role
  FROM bookshop.users u
  WHERE pg_catalog.lower(u.username) = pg_catalog.lower(pg_catalog.btrim(p_username))
     OR (pg_catalog.btrim(u.email) <> '' AND pg_catalog.lower(u.email) = pg_catalog.lower(pg_catalog.btrim(p_username)))
  LIMIT 1
$fn$;

REVOKE ALL ON FUNCTION bookshop.login_record(text) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION bookshop.login_record(text) TO bookshop_app;

COMMIT;
