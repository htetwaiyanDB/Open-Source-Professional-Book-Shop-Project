BEGIN;

-- The existing users_edit RLS policy limits updates to the signed-in user's row
-- (or an administrator). The password hash is never granted for SELECT here.
GRANT UPDATE(password) ON bookshop.users TO bookshop_app;

COMMIT;
