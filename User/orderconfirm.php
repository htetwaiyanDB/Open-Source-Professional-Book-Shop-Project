<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
require_login();

 


// Check if order_id is set in session
if (!isset($_SESSION['order_id'])) {
    die("<div class='alert alert-danger text-center'>Order ID not found. Please place an order first.</div>");
}

$order_id = (int) $_SESSION['order_id'];

// Fetch order details
$order_query = "SELECT * FROM orders WHERE orderid = ? AND customerid = ?";
$order_result = db_query($connection, $order_query, [$order_id, (int) $_SESSION['user_id']]);

if (!$order_result || db_num_rows($order_result) == 0) {
    die("<div class='alert alert-danger text-center'>Order not found.</div>");
}

$order = db_fetch_assoc($order_result);
$customer_name = e($order['deliveryname']);
$customer_email= e($order['email']);
$customer_address = nl2br(e($order['deliveryaddress']));
$customer_phone = e($order['deliveryphone']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Order Confirmation - #<?= $order_id ?></title>

    <!-- Bootstrap 5 CSS -->

    <!-- Font Awesome Icons -->

    <!-- Google Fonts (Optional: Poppins for modern look) -->
    

    <style>

         @media print {
        /* သင့် header ကိုဖျောက်မယ် */
        header, .header, #header, .navbar, .top-bar, nav {
            display: none !important;
        } }

        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            font-weight: 600;
        }
        .table th {
            font-weight: 600;
            color: #495057;
        }
        .total-row {
            font-size: 1.2rem;
            font-weight: 700;
            color: #28a745;
        }
        .icon-box {
            background-color: #e3f2fd;
            padding: 10px;
            border-radius: 8px;
            display: inline-block;
            margin-right: 10px;
        }
        .btn-print {
            background-color: #0d6efd;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
        }
        .btn-print:hover {
            background-color: #0b5ed7;
        }
        footer {
            margin-top: 60px;
            text-align: center;
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<body>

<?php include 'userheader.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <!-- Success Banner -->
            <div class="text-center mb-4">
                <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                <h2 class="mt-3">Thank You, <?= $customer_name ?>!</h2>
                <p class="lead text-muted">Your order has been confirmed and is being processed.</p>
                <p><strong>Order ID:</strong> <code><?= $order_id ?></code></p>
            </div>

            <?php
            $payment_method = ($_SESSION['order_payment'] ?? 'cod') === 'mmqr' ? 'mmqr' : 'cod';
            $paid_demo = !empty($_SESSION['order_paid_demo']);
            ?>
            <div class="alert <?= $payment_method === 'mmqr' ? 'alert-info' : 'alert-secondary' ?>" role="alert">
                <strong>Payment method:</strong>
                <?php if ($payment_method === 'mmqr'): ?>
                    <span class="badge bg-dark">MMQR (demo)</span>
                    <?= $paid_demo
                        ? 'Demo payment recorded. No real money was moved.'
                        : 'No real payment was taken; this is a demo QR.' ?>
                    <a href="orderpay.php" class="alert-link">Show the demo QR again</a>.
                <?php else: ?>
                    <span class="badge bg-secondary">Cash on Delivery</span>
                    Please have the exact amount ready for the courier.
                <?php endif; ?>
            </div>

            <!-- Customer Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                    <div class="icon-box">
                        <i class="fas fa-user text-primary"></i>
                    </div>
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?= $customer_name ?></p>
                            <p><strong>Phone:</strong> <?= $customer_phone ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Address:</strong> <?= $customer_address ?></p>
                            <p><strong>Email:</strong> <?= $customer_email ?></p>
                            
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Details Table -->
            <div class="card">
                <div class="card-header bg-success text-white d-flex align-items-center">
                    <div class="icon-box">
                        <i class="fas fa-box text-success"></i>
                    </div>
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total = 0;
                                $details_query = "
                                    SELECT od.productqty, p.productname, od.unit_price AS price, od.amount 
                                    FROM orderdetails od 
                                    JOIN products p ON od.productid = p.productid 
                                    WHERE od.orderid = ?
                                ";
                                $result = db_query($connection, $details_query, [$order_id]);

                                if (db_num_rows($result) > 0) {
                                    while ($item = db_fetch_assoc($result)) {
                                        $name = $item['productname'];
                                        $price = $item['price'];
                                        $qty = $item['productqty'];
                                        $subtotal = $item['amount'];
                                        $total += $subtotal;
                                        echo "
                                        <tr>
                                            <td><strong>" . e($name) . "</strong></td>
                                            <td>MMK " . e($price) . "</td>
                                            <td><span class='badge bg-primary'>" . e($qty) . "x</span></td>
                                            <td>MMK " . e($subtotal) . "</td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center text-muted'>No items found.</td></tr>";
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                                    <td><strong>MMK <?= number_format($total, 2) ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Print Button -->
                    <div class="text-end mt-3">
                        <button onclick="window.print()" class="btn-print">
                            <i class="fas fa-print"></i> Print Receipt
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<footer class="mb-4">
    <p>&copy; <?= date('Y') ?> YourShop. All rights reserved. | Your satisfaction is our priority.</p>
</footer>

<!-- Bootstrap JS Bundle -->

</body>
</html>
