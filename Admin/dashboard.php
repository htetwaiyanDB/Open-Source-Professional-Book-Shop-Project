<?php
require_once __DIR__ . '/connect.php';
require_admin();

$metrics = db_fetch_assoc(db_query($connection, "
    SELECT
        (SELECT COUNT(*) FROM categorys) AS category_count,
        (SELECT COUNT(*) FROM author) AS author_count,
        (SELECT COUNT(*) FROM users WHERE role = 'user') AS customer_count,
        (SELECT COUNT(*) FROM products) AS book_count,
        (SELECT COALESCE(SUM(qty), 0) FROM products) AS inventory_count,
        (SELECT COUNT(*) FROM products WHERE qty <= 5) AS low_stock_count,
        (SELECT COUNT(*) FROM orders) AS order_count,
        (SELECT COUNT(*) FROM orders WHERE status = 0) AS pending_order_count,
        (SELECT COUNT(*) FROM feedback) AS feedback_count,
        (SELECT COALESCE(SUM(od.amount), 0) FROM orderdetails od JOIN orders o ON o.orderid = od.orderid WHERE o.status = 1) AS delivered_revenue
"));

$recentOrders = db_query($connection, "
    SELECT o.orderid, o.deliveryname, o.orderdate, o.status,
           COALESCE(SUM(od.productqty), 0) AS item_count,
           COALESCE(SUM(od.amount), 0) AS total_amount
    FROM orders o
    LEFT JOIN orderdetails od ON od.orderid = o.orderid
    GROUP BY o.orderid, o.deliveryname, o.orderdate, o.status
    ORDER BY o.orderid DESC
    LIMIT 6
");

$lowStockBooks = db_query($connection, "
    SELECT productid, productname, qty, photo
    FROM products
    WHERE qty <= 5
    ORDER BY qty ASC, productid DESC
    LIMIT 5
");

$adminName = (string) ($_SESSION['admin'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Book Palace Admin</title>
    <link rel="stylesheet" href="../assets/vendor/bootstrap/bootstrap.min.css">
    <script defer src="../assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<body class="admin-dashboard-page">
<a class="admin-skip-link" href="#admin-main">Skip to dashboard content</a>
<?php include 'header.php'; ?>

<main class="admin-dashboard" id="admin-main">
    <header class="dashboard-heading">
        <div>
            <p class="dashboard-kicker">Store overview</p>
            <h1>Good day, <?php echo e($adminName); ?></h1>
            <p>Review orders, inventory, and customer activity across Book Palace.</p>
        </div>
        <div class="dashboard-heading__actions">
            <a class="admin-button admin-button--secondary" href="order_mgmt.php">Review orders</a>
            <a class="admin-button admin-button--primary" href="product.php">Add new book</a>
        </div>
    </header>

    <section class="dashboard-metrics" aria-label="Store performance summary">
        <article class="metric-card">
            <div class="metric-card__header"><span>Pending orders</span><svg aria-hidden="true"><use href="../User/auth-icons.svg#receipt"></use></svg></div>
            <strong><?php echo number_format((int) $metrics['pending_order_count']); ?></strong>
            <p>of <?php echo number_format((int) $metrics['order_count']); ?> total orders</p>
            <a href="order_mgmt.php">Open order queue <svg aria-hidden="true"><use href="../User/auth-icons.svg#arrow-right"></use></svg></a>
        </article>

        <article class="metric-card">
            <div class="metric-card__header"><span>Delivered revenue</span><svg aria-hidden="true"><use href="../User/auth-icons.svg#receipt"></use></svg></div>
            <strong class="metric-card__money"><?php echo number_format((float) $metrics['delivered_revenue'], 0); ?> <small>MMK</small></strong>
            <p>from completed orders</p>
            <a href="order_mgmt.php">View order history <svg aria-hidden="true"><use href="../User/auth-icons.svg#arrow-right"></use></svg></a>
        </article>

        <article class="metric-card">
            <div class="metric-card__header"><span>Books in catalog</span><svg aria-hidden="true"><use href="../User/auth-icons.svg#book"></use></svg></div>
            <strong><?php echo number_format((int) $metrics['book_count']); ?></strong>
            <p><?php echo number_format((int) $metrics['inventory_count']); ?> units in stock</p>
            <a href="productlist.php">Manage inventory <svg aria-hidden="true"><use href="../User/auth-icons.svg#arrow-right"></use></svg></a>
        </article>

        <article class="metric-card <?php echo (int) $metrics['low_stock_count'] > 0 ? 'metric-card--attention' : ''; ?>">
            <div class="metric-card__header"><span>Low-stock titles</span><svg aria-hidden="true"><use href="../User/auth-icons.svg#alert-triangle"></use></svg></div>
            <strong><?php echo number_format((int) $metrics['low_stock_count']); ?></strong>
            <p>titles with 5 units or fewer</p>
            <a href="productlist.php">Review stock levels <svg aria-hidden="true"><use href="../User/auth-icons.svg#arrow-right"></use></svg></a>
        </article>
    </section>

    <div class="dashboard-content-grid">
        <section class="dashboard-panel dashboard-panel--orders" aria-labelledby="recent-orders-title">
            <header class="dashboard-panel__header">
                <div><h2 id="recent-orders-title">Recent orders</h2><p>Latest customer orders and fulfillment status.</p></div>
                <a href="order_mgmt.php">View all orders</a>
            </header>
            <div class="dashboard-table-wrap">
                <table class="dashboard-table">
                    <thead><tr><th scope="col">Order</th><th scope="col">Customer</th><th scope="col">Date</th><th scope="col">Items</th><th scope="col">Total</th><th scope="col">Status</th></tr></thead>
                    <tbody>
                    <?php if (db_num_rows($recentOrders) === 0): ?>
                        <tr><td colspan="6" class="dashboard-empty">No orders have been placed yet.</td></tr>
                    <?php else: ?>
                        <?php while ($order = db_fetch_assoc($recentOrders)): ?>
                            <tr>
                                <td><a class="order-number" href="order_mgmt.php">#<?php echo (int) $order['orderid']; ?></a></td>
                                <td><?php echo e($order['deliveryname']); ?></td>
                                <td><?php echo e(date('M j, Y', strtotime((string) $order['orderdate']))); ?></td>
                                <td><?php echo number_format((int) $order['item_count']); ?></td>
                                <td class="table-money"><?php echo number_format((float) $order['total_amount'], 0); ?> MMK</td>
                                <td><span class="order-status <?php echo (int) $order['status'] === 1 ? 'order-status--done' : 'order-status--pending'; ?>"><?php echo (int) $order['status'] === 1 ? 'Delivered' : 'Pending'; ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="dashboard-panel dashboard-panel--stock" aria-labelledby="stock-title">
            <header class="dashboard-panel__header">
                <div><h2 id="stock-title">Stock attention</h2><p>Titles that may need restocking.</p></div>
            </header>
            <div class="stock-list">
            <?php if (db_num_rows($lowStockBooks) === 0): ?>
                <p class="dashboard-empty">All titles are above the low-stock threshold.</p>
            <?php else: ?>
                <?php while ($book = db_fetch_assoc($lowStockBooks)): ?>
                    <article class="stock-item">
                        <img src="../Photos/<?php echo e(rawurlencode($book['photo'])); ?>" alt="" width="44" height="58" loading="lazy" decoding="async">
                        <div><h3><?php echo e($book['productname']); ?></h3><p><?php echo (int) $book['qty']; ?> units remaining</p></div>
                        <a href="edit.php?action=edit&amp;pid=<?php echo (int) $book['productid']; ?>" aria-label="Edit <?php echo e($book['productname']); ?>">Edit</a>
                    </article>
                <?php endwhile; ?>
            <?php endif; ?>
            </div>
            <a class="panel-footer-link" href="productlist.php">View complete inventory <svg aria-hidden="true"><use href="../User/auth-icons.svg#arrow-right"></use></svg></a>
        </aside>
    </div>

    <section class="catalog-summary" aria-labelledby="catalog-summary-title">
        <header><h2 id="catalog-summary-title">Catalog and customer records</h2><p>Quick access to the supporting data behind daily store operations.</p></header>
        <div class="catalog-summary__items">
            <a href="category.php"><span>Categories</span><strong><?php echo number_format((int) $metrics['category_count']); ?></strong></a>
            <a href="author.php"><span>Authors</span><strong><?php echo number_format((int) $metrics['author_count']); ?></strong></a>
            <a href="userlist.php"><span>Customers</span><strong><?php echo number_format((int) $metrics['customer_count']); ?></strong></a>
            <a href="feedback.php"><span>Feedback entries</span><strong><?php echo number_format((int) $metrics['feedback_count']); ?></strong></a>
        </div>
    </section>
</main>
</body>
</html>
