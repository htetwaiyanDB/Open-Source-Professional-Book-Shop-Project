<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
$user = require_login();
$userId = (int) $_SESSION['user_id'];

$todayOrders = [];
$todayOrderResult = db_query($connection, "
    SELECT o.orderid, o.status, o.orderdate,
           COALESCE(SUM(od.amount), 0) AS total_amount
    FROM orders o
    LEFT JOIN orderdetails od ON od.orderid = o.orderid
    WHERE o.customerid = ? AND o.orderdate = CURRENT_DATE
    GROUP BY o.orderid, o.status, o.orderdate
    ORDER BY o.orderid DESC
", [$userId]);
while ($todayOrder = db_fetch_assoc($todayOrderResult)) {
    $todayOrder['items'] = [];
    $todayOrders[(int) $todayOrder['orderid']] = $todayOrder;
}
if ($todayOrders) {
    $todayItemResult = db_query($connection, '
        SELECT od.orderid, p.productname, od.productqty, od.amount AS line_amount
        FROM orderdetails od
        JOIN orders o ON o.orderid = od.orderid
        JOIN products p ON p.productid = od.productid
        WHERE o.customerid = ? AND o.orderdate = CURRENT_DATE
        ORDER BY od.orderid DESC, p.productname
    ', [$userId]);
    while ($todayItem = db_fetch_assoc($todayItemResult)) {
        $orderId = (int) $todayItem['orderid'];
        if (isset($todayOrders[$orderId])) {
            $todayOrders[$orderId]['items'][] = $todayItem;
        }
    }
}



require_once __DIR__ . '/userfunction.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shopping Cart</title>
<style>
    body {
        background-color: #f0f2f5;
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    }
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .card-header-custom {
        background-color: #fff;
        border-bottom: 1px solid #dee2e6;
        padding: 1.5rem;
    }
    .card-body-custom {
        background-color: #fff;
        padding: 2rem;
    }
    .card-footer-custom {
        background-color: #fff;
        border-top: 1px solid #dee2e6;
        padding: 1.5rem;
    }
    .btn-custom {
        margin: 0.5rem;
        border-radius: 8px;
    }
    .product-img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .table thead th {
        border-bottom: 2px solid #dee2e6;
    }
    .table tfoot td {
        border-top: 2px solid #dee2e6;
    }
    .showname {
        font-size: 1.25rem;
        font-weight: bold;
    }
    .today-orders {
        margin-top: 1.5rem;
        padding: 1.5rem;
        border: 1px solid #dfe4e8;
        border-radius: 8px;
        background: #fff;
    }
    .today-orders__heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e7eaed;
    }
    .today-orders__heading h2 {
        margin: 0;
        font-size: 1.25rem;
        color: #1f2933;
    }
    .today-orders__heading p {
        margin: .35rem 0 0;
        color: #667085;
        font-size: .875rem;
    }
    .today-orders__all {
        color: #067b7f;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }
    .today-orders__all:focus-visible,
    .today-order__link:focus-visible {
        outline: 3px solid rgba(8, 157, 161, .28);
        outline-offset: 3px;
    }
    .today-orders__list {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .today-order {
        padding: 1rem 0;
        border-bottom: 1px solid #edf0f2;
    }
    .today-order:last-child { border-bottom: 0; }
    .today-order__table { width: 100%; table-layout: fixed; border-collapse: collapse; }
    .today-order__table th { padding: .25rem .75rem .65rem 0; color: #667085; font-size: .75rem; font-weight: 600; text-align: left; }
    .today-order__table td { padding: .45rem .75rem .45rem 0; color: #1f2933; font-size: .9rem; line-height: 1.45; vertical-align: top; overflow-wrap: anywhere; }
    .today-order__table th:first-child, .today-order__table td:first-child { width: 58%; }
    .today-order__table th:nth-child(2), .today-order__table td:nth-child(2) { width: 14%; }
    .today-order__table th:last-child, .today-order__table td:last-child { width: 28%; padding-right: 0; }
    .today-order__price { font-weight: 600; white-space: nowrap; }
    .today-order__footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 1rem; margin-top: .65rem; padding-top: .75rem; border-top: 1px solid #edf0f2; }
    .today-order__grand-total { margin-right: auto; color: #1f2933; font-size: .9rem; }
    .today-order__status {
        width: fit-content;
        padding: .3rem .65rem;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 700;
    }
    .today-order__status--processing { color: #8a4b08; background: #fff3dd; }
    .today-order__status--delivered { color: #116044; background: #e8f7f0; }
    .today-order__link { color: #067b7f; font-size: .875rem; font-weight: 700; text-decoration: none; }
    .today-orders__empty { margin: 0; padding: 1.25rem 0 .25rem; color: #667085; }
    @media (max-width: 700px) {
        .today-orders__heading { flex-direction: column; }
        .today-order__table th:first-child, .today-order__table td:first-child { width: 50%; }
        .today-order__table th:nth-child(2), .today-order__table td:nth-child(2) { width: 18%; }
        .today-order__table th:last-child, .today-order__table td:last-child { width: 32%; }
    }
    @media (max-width: 420px) {
        .today-orders { padding: 1.1rem; }
        .today-order__table th, .today-order__table td { padding-right: .4rem; font-size: .78rem; }
        .today-order__price { white-space: normal; }
        .today-order__grand-total { width: 100%; }
    }
</style>
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<body>
<?php include 'userheader.php'; echo render_flash(); ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
           
              
                <div class="card-body card-body-custom">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-3">
                                <div class="card-header text-center bg-info text-white">
                                    <h2>Shopping Cart</h2>
                                </div>
                                <?php if (!empty($_SESSION['cart'])) : ?>
                                    <div class="card-body">
                                        <div class="responsive-table-wrap" role="region" aria-label="Shopping cart items" tabindex="0">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Photo</th>
                                                    <th>Name</th>
                                                    <th>Quantity</th>
                                                    <th>Unit Price</th>
                                                    <th>Price</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $total = 0;
                                                foreach ($_SESSION['cart'] as $id => $qty) {
                                                    $id = (int) $id;
                                                    $qty = max(1, (int) $qty);
                                                    $result = db_query($connection, 'SELECT * FROM products WHERE productid = ?', [$id]);

                                                    if ($result && db_num_rows($result) > 0) {
                                                        $row = db_fetch_assoc($result);
                                                ?>
                                                        <tr>
                                                            <td><img src="../Photos/<?php echo e($row['photo']); ?>" class="img-thumbnail product-img" /></td>
                                                            <td><?php echo e($row['productname']); ?></td>
                                                            <td>
                                                                <span>
                                                                    <form action="change-qty.php" method="post" style="display:inline"><?php echo csrf_field(); ?><input type="hidden" name="id" value="<?php echo (int) $id; ?>"><input type="hidden" name="action" value="increase"><button type="submit" class="btn btn-outline-success btn-sm"><i class="fas fa-plus"></i></button></form>
                                                                    <?php echo e($qty); ?>
                                                                    <form action="change-qty.php" method="post" style="display:inline"><?php echo csrf_field(); ?><input type="hidden" name="id" value="<?php echo (int) $id; ?>"><input type="hidden" name="action" value="decrease"><button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-minus"></i></button></form>
                                                                </span>
                                                            </td>
                                                            <td>MMK-<?php echo number_format($row['price'], 2) ?></td>
                                                            <td>MMK-<?php echo number_format($row['price'] * $qty, 2) ?></td>
                                                            <td>
                                                                <form action="remove.php" method="post" style="display:inline"><?php echo csrf_field(); ?><input type="hidden" name="id" value="<?php echo (int) $id; ?>"><button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i> Remove</button></form>
                                                            </td>
                                                        </tr>
                                                <?php
                                                        $total += $row['price'] * $qty;
                                                    } else {
                                                        echo 'This book is no longer available.';
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="4" class="text-end"><b>Total:</b></td>
                                                    <td>MMK-<?php echo number_format($total, 2); ?></td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                        </div>
                                    </div>
                                    <div class="card-footer card-footer-custom text-center">
                                        <form action="clear.php" method="post" style="display:inline"><?php echo csrf_field(); ?><button type="submit" class="btn btn-danger btn-custom">Clear Cart</button></form>
                                        <a href="userindex.php" class="btn btn-secondary btn-custom">Back</a>
                                        <a href="submitorder.php" class="btn btn-primary btn-custom">Submit Order</a>
                                    </div>
                                <?php else : ?>
                                    <div class="card-body text-center">
                                        <h3 class="text-danger lead">You have not selected any products yet!</h3>
                                        <p><a href="userindex.php" class="btn btn-info btn-custom">Shop Now</a></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <section class="today-orders" aria-labelledby="today-orders-title">
                        <div class="today-orders__heading">
                            <div>
                                <h2 id="today-orders-title">Today's orders</h2>
                                <p>Only orders placed today are shown here.</p>
                            </div>
                            <a class="today-orders__all" href="userProfile.php#order-tracking">View all order history</a>
                        </div>
                        <?php if ($todayOrders): ?>
                            <div class="today-orders__list">
                                <?php foreach ($todayOrders as $todayOrder):
                                    $isDelivered = (int) $todayOrder['status'] === 1;
                                ?>
                                    <article class="today-order">
                                        <table class="today-order__table">
                                            <thead><tr><th scope="col">Items name</th><th scope="col">Quantity</th><th scope="col">Pricing</th></tr></thead>
                                            <tbody>
                                                <?php if ($todayOrder['items']): ?>
                                                    <?php foreach ($todayOrder['items'] as $item): ?>
                                                        <tr>
                                                            <td><?php echo e($item['productname']); ?></td>
                                                            <td><?php echo e($item['productqty']); ?></td>
                                                            <td class="today-order__price"><?php echo e(number_format((float) $item['line_amount'], 2)); ?> MMK</td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr><td>No item details available.</td><td>0</td><td class="today-order__price">0.00 MMK</td></tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                        <footer class="today-order__footer">
                                            <strong class="today-order__grand-total">Order total: <?php echo e(number_format((float) $todayOrder['total_amount'], 2)); ?> MMK</strong>
                                            <span class="today-order__status today-order__status--<?php echo $isDelivered ? 'delivered' : 'processing'; ?>"><?php echo $isDelivered ? 'Delivered' : 'Processing'; ?></span>
                                            <a class="today-order__link" href="userProfile.php#order-<?php echo e($todayOrder['orderid']); ?>">Track</a>
                                        </footer>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="today-orders__empty">You have not placed an order today. Your older orders are available in My Account.</p>
                        <?php endif; ?>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
