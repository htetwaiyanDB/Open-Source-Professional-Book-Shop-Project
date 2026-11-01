-- Run AFTER migration + catalog seed with psql -X -v ON_ERROR_STOP=1 -f ...
-- Uses real bookshop_app privileges/RLS. Everything (including fixtures) rolls back.
\set ON_ERROR_STOP on
BEGIN;
CREATE FUNCTION pg_temp.assert_true(ok boolean, message text) RETURNS void
LANGUAGE plpgsql AS $$ BEGIN
  IF ok IS DISTINCT FROM true THEN RAISE EXCEPTION 'FAIL: %',message; END IF;
END $$;
CREATE FUNCTION pg_temp.expect_error(statement text, expected_state text) RETURNS void
LANGUAGE plpgsql AS $$ BEGIN
  BEGIN EXECUTE statement;
  EXCEPTION WHEN OTHERS THEN
    IF SQLSTATE = expected_state THEN RETURN; END IF;
    RAISE EXCEPTION 'Wrong SQLSTATE: expected %, got % (%) for %',expected_state,SQLSTATE,SQLERRM,statement;
  END;
  RAISE EXCEPTION 'Expected failure % but statement succeeded: %',expected_state,statement;
END $$;

INSERT INTO bookshop.users(userid,username,password,email,phone,address,role) VALUES
 (900001,'__sql_customer_a','$2y$10$'||repeat('A',53),'a@example.invalid','123','A','user'),
 (900002,'__sql_customer_b','$2y$10$'||repeat('B',53),'b@example.invalid','456','B','user'),
 (900003,'__sql_admin','$2y$10$'||repeat('C',53),'admin@example.invalid','789','C','admin');
INSERT INTO bookshop.products(productid,productname,categoryid,price,qty,photo,authorid,description)
 SELECT 900001,'__sql_stock',min(c.catid),12.50,5,'',min(a.authorid),'' FROM bookshop.categorys c CROSS JOIN bookshop.author a;
INSERT INTO bookshop.products(productid,productname,categoryid,price,qty,photo,authorid,description)
 SELECT 900002,'__sql_empty',min(c.catid),99,0,'',min(a.authorid),'' FROM bookshop.categorys c CROSS JOIN bookshop.author a;

SELECT pg_temp.assert_true((SELECT count(*)=9 FROM pg_catalog.pg_class t JOIN pg_catalog.pg_namespace n ON n.oid=t.relnamespace
  WHERE n.nspname='bookshop' AND t.relkind='r' AND t.relrowsecurity AND pg_catalog.pg_get_userbyid(t.relowner)='postgres'), 'nine RLS tables owned by postgres');
SELECT pg_temp.assert_true((SELECT NOT rolsuper AND NOT rolbypassrls AND NOT rolcreaterole AND NOT rolcreatedb AND rolcanlogin
  FROM pg_catalog.pg_roles WHERE rolname='bookshop_app'),'runtime role restricted');
SELECT pg_temp.assert_true(NOT EXISTS (SELECT FROM pg_catalog.pg_auth_members WHERE member=(SELECT oid FROM pg_catalog.pg_roles WHERE rolname='bookshop_app')), 'runtime has no SET ROLE memberships');
SELECT pg_temp.assert_true(NOT EXISTS (SELECT FROM pg_catalog.pg_proc p JOIN pg_catalog.pg_namespace n ON n.oid=p.pronamespace
  WHERE n.nspname='bookshop' AND (pg_catalog.pg_get_userbyid(p.proowner)<>'postgres' OR NOT ('search_path=""'=ANY(p.proconfig)))), 'all helpers postgres-owned and search path pinned');
DO $$ DECLARE r text; BEGIN
  FOREACH r IN ARRAY ARRAY['anon','authenticated','service_role'] LOOP
    IF EXISTS(SELECT FROM pg_catalog.pg_roles WHERE rolname=r) THEN
      PERFORM pg_temp.assert_true(NOT pg_catalog.has_schema_privilege(r,'bookshop','USAGE'),r||' no private schema access');
      PERFORM pg_temp.assert_true(NOT pg_catalog.has_function_privilege(r,'bookshop.login_record(text)','EXECUTE'),r||' no auth lookup');
    END IF;
  END LOOP;
END $$;

SET LOCAL ROLE bookshop_app;
SELECT pg_catalog.set_config('app.user_id','',true);
SELECT pg_temp.assert_true(bookshop.current_user_id() IS NULL AND NOT bookshop.is_admin(),'anonymous context');
SELECT pg_temp.assert_true((SELECT count(*)>0 FROM bookshop.products),'anonymous catalog');
SELECT pg_temp.assert_true((SELECT count(*)=0 FROM bookshop.users),'anonymous no users');
SELECT pg_temp.assert_true((SELECT count(*)=0 FROM bookshop.orders),'anonymous no orders');
SELECT pg_temp.assert_true((SELECT count(*)=0 FROM bookshop.orderdetails),'anonymous no details');
SELECT pg_temp.expect_error('SELECT * FROM bookshop.users','42501');
SELECT pg_temp.expect_error('SELECT password FROM bookshop.users','42501');
SELECT pg_temp.expect_error('SELECT bookshop.checkout(''[{"productid":900001,"qty":1}]''::jsonb,''A'','''',''1'',''A'')','42501');
SELECT pg_temp.expect_error('INSERT INTO bookshop.comments(productid,userid,content) VALUES(900001,900001,''spoof'')','42501');
INSERT INTO bookshop.feedback(name,email,phone,message) VALUES('__sql_feedback','test@example.invalid','','anonymous feedback');
SELECT pg_temp.assert_true((SELECT count(*)=0 FROM bookshop.feedback),'anonymous feedback write-only');
SELECT pg_temp.assert_true((SELECT count(*)=1 FROM bookshop.login_record('__SQL_CUSTOMER_A')),'explicit auth lookup callable case insensitive');
SELECT pg_temp.expect_error('SELECT bookshop.register_user(''__bad_hash'',''plaintext'','''','''','''')','23514');
SELECT bookshop.register_user('__sql_registered','$2y$10$'||repeat('D',53),'','','') AS registered \gset

-- Malformed identity cannot cast-error or become admin. A valid numeric forged
-- identity IS trusted: PHP/session + no SQL injection is the boundary, not GUC ACL.
SELECT pg_catalog.set_config('app.user_id','900003 OR 1=1',true);
SELECT pg_temp.assert_true(bookshop.current_user_id() IS NULL AND NOT bookshop.is_admin(),'malicious identity rejected');
SELECT pg_catalog.set_config('app.user_id','9999999999999999999',true);
SELECT pg_temp.assert_true(bookshop.current_user_id() IS NULL,'overflow rejected');
SELECT pg_catalog.set_config('app.user_id','-1',true);
SELECT pg_temp.assert_true(bookshop.current_user_id() IS NULL,'negative rejected');
SELECT pg_catalog.set_config('app.user_id','900099',true);
SELECT pg_temp.expect_error('SELECT bookshop.checkout(''[{"productid":900001,"qty":1}]''::jsonb,''A'','''',''1'',''A'')','42501');

SELECT pg_catalog.set_config('app.user_id','900001',true);
SELECT pg_temp.assert_true((SELECT count(*)=1 FROM bookshop.users),'customer sees self only');
SELECT pg_temp.assert_true(NOT bookshop.is_admin(),'customer not admin (nonrecursive users RLS)');
SELECT pg_temp.expect_error('UPDATE bookshop.users SET role=''admin'' WHERE userid=900001','42501');
SELECT pg_temp.expect_error('UPDATE bookshop.users SET password=''x'' WHERE userid=900001','42501');
SELECT pg_temp.expect_error('INSERT INTO bookshop.users(username,password,role) VALUES(''__evil'',''x'',''admin'')','42501');
SELECT pg_temp.expect_error('SELECT bookshop.admin_create_user(''__evil'',''x'','''','''','''',''admin'')','42501');
SELECT pg_temp.expect_error('SELECT bookshop.admin_delete_user(900002)','42501');
UPDATE bookshop.users SET address='safe self update' WHERE userid=900001;
UPDATE bookshop.users SET address='forbidden cross-user update' WHERE userid=900002;
SELECT pg_temp.assert_true((SELECT address='safe self update' FROM bookshop.users WHERE userid=900001),'self update allowed');
UPDATE bookshop.products SET qty=999 WHERE productid=900001;
SELECT pg_temp.assert_true((SELECT qty=5 FROM bookshop.products WHERE productid=900001),'customer cannot update stock');
SELECT pg_temp.expect_error('INSERT INTO bookshop.orders(customerid,deliveryname,deliveryphone,deliveryaddress) VALUES(900001,''A'',''1'',''A'')','42501');
SELECT pg_temp.expect_error('INSERT INTO bookshop.orderdetails(orderid,productid,productqty,unit_price,amount) VALUES(1,900001,1,0,0)','42501');
INSERT INTO bookshop.comments(productid,userid,content) VALUES(900001,900001,'__sql_comment');
SELECT pg_temp.expect_error('INSERT INTO bookshop.comments(productid,userid,content) VALUES(900001,900002,''spoof'')','42501');

-- Strict cart shape, forged price rejection, stock and unknown-product atomicity.
SELECT pg_temp.expect_error('SELECT bookshop.checkout(''{}''::jsonb,''A'','''',''1'',''A'')','22023');
SELECT pg_temp.expect_error('SELECT bookshop.checkout(''[]''::jsonb,''A'','''',''1'',''A'')','22023');
SELECT pg_temp.expect_error('SELECT bookshop.checkout(''[{"productid":900001,"qty":0}]''::jsonb,''A'','''',''1'',''A'')','22023');
SELECT pg_temp.expect_error('SELECT bookshop.checkout(''[{"productid":900001,"qty":1.5}]''::jsonb,''A'','''',''1'',''A'')','22023');
SELECT pg_temp.expect_error('SELECT bookshop.checkout(''[{"productid":"900001","qty":1}]''::jsonb,''A'','''',''1'',''A'')','22023');
SELECT pg_temp.expect_error('SELECT bookshop.checkout(''[{"productid":900001,"qty":1,"price":0}]''::jsonb,''A'','''',''1'',''A'')','22023');
SELECT pg_temp.expect_error('SELECT bookshop.checkout(''[{"productid":900001,"qty":1},{"productid":900001,"qty":1}]''::jsonb,''A'','''',''1'',''A'')','22023');
SELECT pg_temp.expect_error('SELECT bookshop.checkout(''[{"productid":900001,"qty":1},{"productid":900002,"qty":1}]''::jsonb,''A'','''',''1'',''A'')','22023');
SELECT pg_temp.expect_error('SELECT bookshop.checkout(''[{"productid":900001,"qty":1},{"productid":99999999,"qty":1}]''::jsonb,''A'','''',''1'',''A'')','22023');
SELECT pg_temp.assert_true((SELECT qty=5 FROM bookshop.products WHERE productid=900001),'failed checkout unchanged inventory');
SELECT pg_temp.assert_true((SELECT count(*)=0 FROM bookshop.orders WHERE customerid=900001),'failed checkout no order');
SELECT bookshop.checkout('[{"productid":900001,"qty":2}]','A','a@example.invalid','123','A') AS order_a \gset
SELECT pg_temp.assert_true((SELECT qty=3 FROM bookshop.products WHERE productid=900001),'checkout decrements stock');
SELECT pg_temp.assert_true((SELECT customerid=900001 FROM bookshop.orders WHERE orderid=:order_a),'checkout bound to setting user');
SELECT pg_temp.assert_true((SELECT productqty=2 AND unit_price=12.50 AND amount=25 FROM bookshop.orderdetails WHERE orderid=:order_a),'server-priced snapshot');
SAVEPOINT rollback_checkout;
SELECT bookshop.checkout('[{"productid":900001,"qty":1}]','A','','123','A');
ROLLBACK TO SAVEPOINT rollback_checkout;
SELECT pg_temp.assert_true((SELECT qty=3 FROM bookshop.products WHERE productid=900001),'outer transaction rollback restores stock');
SELECT pg_temp.assert_true((SELECT count(*)=1 FROM bookshop.orders WHERE customerid=900001),'outer transaction rollback restores orders');

SELECT pg_catalog.set_config('app.user_id','900002',true);
SELECT pg_temp.assert_true((SELECT count(*)=0 FROM bookshop.orders WHERE orderid=:order_a),'cross-user order hidden');
SELECT pg_temp.assert_true((SELECT count(*)=0 FROM bookshop.orderdetails WHERE orderid=:order_a),'cross-user details hidden');
SELECT pg_temp.assert_true((SELECT address='B' FROM bookshop.users WHERE userid=900002),'cross-user edit did not happen');
UPDATE bookshop.orders SET status=1 WHERE orderid=:order_a;
DELETE FROM bookshop.comments WHERE content='__sql_comment';
SELECT pg_temp.assert_true((SELECT count(*)=1 FROM bookshop.comments WHERE content='__sql_comment'),'customer cannot delete comment');

SELECT pg_catalog.set_config('app.user_id','900003',true);
SELECT pg_temp.assert_true(bookshop.is_admin(),'admin role from server-owned users');
SELECT pg_temp.assert_true((SELECT role='user' FROM bookshop.users WHERE userid=:registered),'register role forced user');
SELECT pg_temp.assert_true((SELECT count(*)=3 FROM bookshop.users WHERE userid BETWEEN 900001 AND 900003),'admin sees all users');
SELECT pg_temp.assert_true((SELECT status=0 FROM bookshop.orders WHERE orderid=:order_a),'customer cannot change order status');
UPDATE bookshop.orders SET status=1,senddate=CURRENT_DATE WHERE orderid=:order_a;
SELECT pg_temp.assert_true((SELECT status=1 FROM bookshop.orders WHERE orderid=:order_a),'admin updates order');
UPDATE bookshop.products SET qty=4 WHERE productid=900001;
SELECT pg_temp.assert_true((SELECT qty=4 FROM bookshop.products WHERE productid=900001),'admin inventory update');
SELECT pg_temp.expect_error('UPDATE bookshop.products SET qty=-1 WHERE productid=900001','23514');
SELECT pg_temp.expect_error('UPDATE bookshop.products SET price=-1 WHERE productid=900001','23514');
SELECT pg_temp.expect_error('SELECT password FROM bookshop.users','42501');
SELECT pg_temp.expect_error('SELECT bookshop.admin_delete_user(900003)','22023');
SELECT pg_temp.expect_error('SELECT bookshop.admin_delete_user(900001)','23503');
SELECT bookshop.admin_create_user('__sql_created_admin','$2y$10$'||repeat('E',53),'','','','admin') AS created_admin \gset
SELECT pg_temp.assert_true((SELECT role='admin' FROM bookshop.users WHERE userid=:created_admin),'admin can explicitly create admin');
SELECT bookshop.admin_delete_user(:created_admin);
SELECT pg_temp.assert_true((SELECT count(*)=1 FROM bookshop.feedback WHERE name='__sql_feedback'),'admin reads feedback');
DELETE FROM bookshop.feedback WHERE name='__sql_feedback';
DELETE FROM bookshop.comments WHERE content='__sql_comment';
SELECT pg_temp.assert_true((SELECT count(*)=0 FROM bookshop.comments WHERE content='__sql_comment'),'admin deletes comment');
RESET ROLE;
ROLLBACK;
\echo 'PASS: private schema, grants, identity, RLS, escalation, checkout validation, prices, stock and rollback'