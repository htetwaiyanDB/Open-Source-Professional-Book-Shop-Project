<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
require_login();

// Demo payment confirmation only: the QR popup posts here and this sets a
// session flag. No provider has confirmed a transfer and the runtime database
// role cannot update order state, so nothing is marked as paid in PostgreSQL.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'paid') {
    $_SESSION['order_paid_demo'] = true;
    redirect('orderconfirm.php');
}

 


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

// The stored choice lives in orders.payment_method; the demo "paid" state is
// session-only and never written back.
$payment_method = ($_SESSION['order_payment'] ?? 'cod') === 'mmqr' ? 'mmqr' : 'cod';
$paid_demo = !empty($_SESSION['order_paid_demo']);

// Materialise the order lines once so the summary table and the QR popup share
// one server-calculated total.
$order_items = [];
$total = 0.0;
$details_result = db_query($connection, '
    SELECT od.productqty, p.productname, od.unit_price AS price, od.amount
    FROM orderdetails od
    JOIN products p ON od.productid = p.productid
    WHERE od.orderid = ?
', [$order_id]);
while ($item = db_fetch_assoc($details_result)) {
    $order_items[] = $item;
    $total += (float) $item['amount'];
}

// Popup QR is only built for the demo MMQR flow that has not been "paid" yet.
$show_pay_popup = $payment_method === 'mmqr' && !$paid_demo && isset($_GET['pay']);
$qr_amount = number_format($total, 2);
// Placeholder payload: a live integration would render the EMVCo MMQR string
// returned by the provider for this order instead of this demo text.
$qr_payload = 'MMPAY-DEMO|order=' . $order_id . '|amount=' . $qr_amount . '|currency=MMK|merchant=Book%20Palace';
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
        }
        .modal, .modal-backdrop { display: none !important; } }

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

        /* Payment summary + MMQR popup */
        .text-teal { color: #089da1 !important; }
        .icon-box-teal { background-color: #e6f5f5; }
        .icon-box-grey { background-color: #eef1f4; }
        .btn-pay { background-color: #089da1; border-color: #089da1; }
        .btn-pay:hover, .btn-pay:focus { background-color: #077b7e; border-color: #077b7e; color: #fff; }
        .mmqr-modal .modal-content { border: 0; border-radius: 18px; overflow: hidden; }
        .mmqr-modal .modal-header {
            border-bottom: 0;
            background: linear-gradient(135deg, #089da1, #066f73);
            color: #fff;
        }
        .mmqr-modal .modal-header .btn-close { filter: invert(1) grayscale(1) brightness(2); opacity: .85; }
        .mmqr-frame {
            display: inline-block;
            padding: 14px;
            background: #fff;
            border: 1px solid #e3e6ea;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .06);
        }
        .mmqr-frame svg { display: block; width: 210px; height: 210px; }
        .mmqr-amount { font-size: 1.35rem; font-weight: 700; color: #1f2d3d; }
        .mmqr-app {
            background: #f1f3f5;
            border-radius: 999px;
            padding: 4px 12px;
            font-size: .78rem;
            color: #495057;
        }
        .mmqr-modal .modal-footer {
            border-top: 1px solid #eef1f4;
            background: #fbfcfd;
            flex-wrap: nowrap;
            gap: .5rem;
        }
        .mmqr-modal .modal-footer > * { flex: 1 1 0; margin: 0; }
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

            <!-- Payment Summary -->
            <div class="card mb-4">
                <div class="card-body d-flex flex-wrap align-items-center gap-3">
                    <div class="icon-box <?= $payment_method === 'mmqr' ? 'icon-box-teal' : 'icon-box-grey' ?>">
                        <i class="fas <?= $payment_method === 'mmqr' ? 'fa-qrcode text-teal' : 'fa-money-bill-wave text-secondary' ?>" aria-hidden="true"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold d-flex align-items-center gap-2">
                            <?= $payment_method === 'mmqr' ? 'MMQR' : 'Cash on Delivery' ?>
                            <?php if ($payment_method === 'mmqr'): ?>
                                <span class="badge rounded-pill bg-warning text-dark">Demo</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted small">
                            <?php if ($payment_method === 'mmqr'): ?>
                                <?= $paid_demo
                                    ? 'Demo payment recorded &mdash; no real money was moved.'
                                    : 'Scan the QR code with KBZPay, WavePay, AYA Pay or CB Pay to finish the demo payment.' ?>
                            <?php else: ?>
                                Please have <strong>MMK <?= e(number_format($total, 2)) ?></strong> ready for the courier when your books arrive.
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="text-end">
                        <?php if ($payment_method === 'mmqr'): ?>
                            <span class="badge <?= $paid_demo ? 'bg-success' : 'bg-secondary' ?> d-block mb-2">
                                <?= $paid_demo ? 'Payment simulated' : 'Awaiting payment' ?>
                            </span>
                            <?php if (!$paid_demo): ?>
                                <a href="orderconfirm.php?pay=1" class="btn btn-sm btn-pay text-white">
                                    <i class="fas fa-qrcode me-1" aria-hidden="true"></i> Show QR
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge bg-secondary">Pay on delivery</span>
                        <?php endif; ?>
                    </div>
                </div>
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
                                <?php if (empty($order_items)): ?>
                                    <tr><td colspan="4" class="text-center text-muted">No items found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td><strong><?= e($item['productname']) ?></strong></td>
                                            <td>MMK <?= e($item['price']) ?></td>
                                            <td><span class="badge bg-primary"><?= e($item['productqty']) ?>x</span></td>
                                            <td>MMK <?= e($item['amount']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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

<?php if ($show_pay_popup): ?>
<!-- MMQR demo popup: opens automatically once an MMQR order is placed -->
<div class="modal fade mmqr-modal" id="mmqrPayPopup" tabindex="-1" aria-labelledby="mmqrPayPopupLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2" id="mmqrPayPopupLabel">
                    <i class="fas fa-qrcode" aria-hidden="true"></i> Scan to pay with MMQR
                    <span class="badge rounded-pill bg-warning text-dark">Demo</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="text-muted small mb-3">
                    Open KBZPay, WavePay, AYA Pay or CB Pay on your phone, then scan this code.
                </p>
                <div class="mmqr-frame mb-3">
                    <div id="mmqr-qr" class="text-muted small">Rendering QR&hellip;</div>
                </div>
                <p class="mmqr-amount mb-1">MMK <?= e($qr_amount) ?></p>
                <p class="text-muted small mb-3">Order <code>#<?= $order_id ?></code></p>
                <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
                    <span class="mmqr-app">KBZPay</span>
                    <span class="mmqr-app">WavePay</span>
                    <span class="mmqr-app">AYA Pay</span>
                    <span class="mmqr-app">CB Pay</span>
                    <span class="mmqr-app">Any MMQR app</span>
                </div>
                <div class="alert alert-warning text-start small mb-0" role="alert">
                    <strong>Demo only.</strong> This QR is generated in your browser and is not linked to a payment
                    provider. No money moves and we never ask for card numbers or banking credentials.
                </div>
            </div>
            <div class="modal-footer">
                <a href="orderconfirm.php" class="btn btn-outline-secondary">Pay later</a>
                <form action="orderconfirm.php" method="post" class="d-flex">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="paid">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-check me-1" aria-hidden="true"></i> I have paid
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="../assets/vendor/qrcode/qrcode.js"></script>
<script>
    (function () {
        var target = document.getElementById('mmqr-qr');
        var payload = <?php echo json_encode($qr_payload, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        try {
            var qr = qrcode(0, 'M');
            qr.addData(payload);
            qr.make();
            target.innerHTML = qr.createSvgTag({ cellSize: 6, margin: 2 });
        } catch (error) {
            target.textContent = 'QR could not be rendered in this browser.';
        }

        // Bootstrap loads with defer from the header, so wait for DOMContentLoaded.
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.bootstrap) { return; }
            bootstrap.Modal.getOrCreateInstance(document.getElementById('mmqrPayPopup')).show();
        });
    }());
</script>
<?php endif; ?>

<!-- Bootstrap JS Bundle -->

</body>
</html>
