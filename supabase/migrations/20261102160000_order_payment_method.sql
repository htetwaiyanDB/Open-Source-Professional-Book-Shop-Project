BEGIN;

-- Checkout now records how the customer intends to pay. Cash on delivery is the
-- default so existing rows and older callers keep the previous behaviour; MMQR
-- is the online option shown on the order form.
ALTER TABLE bookshop.orders
  ADD COLUMN payment_method text NOT NULL DEFAULT 'cod'
  CHECK (payment_method IN ('cod','mmqr'));

-- Replace the five-argument checkout with a six-argument version. The new
-- argument defaults to 'cod', so a caller that still passes five values keeps
-- working, and the value is validated inside the definer before it is stored.
-- Only the method is accepted here: no price, status or total comes from PHP.
REVOKE ALL ON FUNCTION bookshop.checkout(jsonb,text,text,text,text) FROM PUBLIC;
DROP FUNCTION bookshop.checkout(jsonb,text,text,text,text);

CREATE FUNCTION bookshop.checkout(p_cart jsonb, p_deliveryname text, p_email text, p_deliveryphone text, p_address text, p_payment_method text DEFAULT 'cod')
RETURNS bigint LANGUAGE plpgsql SECURITY DEFINER SET search_path = '' AS $fn$
DECLARE
  customer bigint := bookshop.current_user_id();
  item jsonb;
  requested record;
  stock record;
  new_order bigint;
  n integer := 0;
BEGIN
  IF customer IS NULL OR NOT EXISTS (SELECT FROM bookshop.users WHERE userid = customer) THEN
    RAISE EXCEPTION 'Authenticated customer required' USING ERRCODE = '42501';
  END IF;
  IF p_cart IS NULL OR pg_catalog.jsonb_typeof(p_cart) <> 'array' THEN
    RAISE EXCEPTION 'Cart must be an array' USING ERRCODE = '22023';
  END IF;
  IF pg_catalog.jsonb_array_length(p_cart) NOT BETWEEN 1 AND 100 THEN
    RAISE EXCEPTION 'Cart must contain 1 to 100 lines' USING ERRCODE = '22023';
  END IF;
  FOR item IN SELECT value FROM pg_catalog.jsonb_array_elements(p_cart) LOOP
    IF pg_catalog.jsonb_typeof(item) <> 'object' OR
      NOT (item ? 'productid' AND item ? 'qty') OR
      (SELECT count(*) FROM pg_catalog.jsonb_object_keys(item)) <> 2 OR
      pg_catalog.jsonb_typeof(item->'productid') <> 'number' OR
      pg_catalog.jsonb_typeof(item->'qty') <> 'number' OR
      (item->>'productid') !~ '^[1-9][0-9]{0,17}$' OR
      (item->>'qty') !~ '^[1-9][0-9]{0,2}$' THEN
      RAISE EXCEPTION 'Invalid cart line (numeric productid and qty 1..999 only)' USING ERRCODE = '22023';
    END IF;
  END LOOP;
  IF (SELECT count(DISTINCT value->>'productid') FROM pg_catalog.jsonb_array_elements(p_cart))
      <> pg_catalog.jsonb_array_length(p_cart) THEN
    RAISE EXCEPTION 'Duplicate product in cart' USING ERRCODE = '22023';
  END IF;
  IF p_deliveryname IS NULL OR char_length(pg_catalog.btrim(p_deliveryname)) NOT BETWEEN 1 AND 255
    OR p_deliveryphone IS NULL OR char_length(pg_catalog.btrim(p_deliveryphone)) NOT BETWEEN 1 AND 30
    OR p_address IS NULL OR char_length(pg_catalog.btrim(p_address)) NOT BETWEEN 1 AND 1000
    OR p_email IS NULL OR char_length(p_email) > 254 THEN
    RAISE EXCEPTION 'Invalid customer delivery details' USING ERRCODE = '22023';
  END IF;
  IF p_payment_method IS NULL OR p_payment_method NOT IN ('cod','mmqr') THEN
    RAISE EXCEPTION 'Unsupported payment method' USING ERRCODE = '22023';
  END IF;
  -- Acquire every inventory lock in deterministic ID order before any write.
  FOR stock IN
    SELECT p.productid,p.qty FROM bookshop.products p
      JOIN pg_catalog.jsonb_to_recordset(p_cart) AS c(productid bigint,qty integer)
        ON c.productid = p.productid
      ORDER BY p.productid FOR UPDATE OF p
  LOOP
    n := n + 1;
    SELECT c.qty INTO requested FROM pg_catalog.jsonb_to_recordset(p_cart) AS c(productid bigint,qty integer)
      WHERE c.productid = stock.productid;
    IF requested.qty > stock.qty THEN
      RAISE EXCEPTION 'Insufficient stock for product %', stock.productid USING ERRCODE = '22023';
    END IF;
  END LOOP;
  IF n <> pg_catalog.jsonb_array_length(p_cart) THEN
    RAISE EXCEPTION 'Unknown cart product' USING ERRCODE = '22023';
  END IF;
  INSERT INTO bookshop.orders(customerid,deliveryname,email,deliveryphone,deliveryaddress,payment_method)
    VALUES(customer,pg_catalog.btrim(p_deliveryname),p_email,pg_catalog.btrim(p_deliveryphone),pg_catalog.btrim(p_address),p_payment_method)
    RETURNING orderid INTO new_order;
  INSERT INTO bookshop.orderdetails(orderid,productid,productqty,unit_price,amount)
    SELECT new_order,p.productid,c.qty,p.price,p.price*c.qty
    FROM bookshop.products p
    JOIN pg_catalog.jsonb_to_recordset(p_cart) AS c(productid bigint,qty integer) ON p.productid=c.productid;
  UPDATE bookshop.products p SET qty=p.qty-c.qty
    FROM pg_catalog.jsonb_to_recordset(p_cart) AS c(productid bigint,qty integer)
    WHERE p.productid=c.productid;
  RETURN new_order;
END
$fn$;

REVOKE ALL ON FUNCTION bookshop.checkout(jsonb,text,text,text,text,text) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION bookshop.checkout(jsonb,text,text,text,text,text) TO bookshop_app;

COMMIT;
