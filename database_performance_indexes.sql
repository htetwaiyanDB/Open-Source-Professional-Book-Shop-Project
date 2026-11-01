-- Indexes for the project's most frequent login, catalog, join, and order lookups.
ALTER TABLE `user`
    ADD INDEX `idx_user_login` (`username`, `role`, `password`);

ALTER TABLE `products`
    ADD INDEX `idx_products_category` (`categoryid`),
    ADD INDEX `idx_products_author` (`authorid`),
    ADD INDEX `idx_products_description_author` (`description`, `authorid`);

ALTER TABLE `orders`
    ADD INDEX `idx_orders_customer` (`customerid`);

ALTER TABLE `orderdetails`
    ADD INDEX `idx_orderdetails_order` (`orderid`),
    ADD INDEX `idx_orderdetails_product` (`productid`);
