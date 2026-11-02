<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
require_login();

if (empty($_SESSION['order_id'])) {
    flash_error('Place an order before opening the payment page.');
    redirect('cart.php');
}
$order_id = (int) $_SESSION['order_id'];
$payment = ($_SESSION['order_payment'] ?? 'cod') === 'mmqr' ? 'mmqr' : 'cod';

// Demo confirmation only: nothing here marks an order as paid in the database,
// because the runtime role cannot update orders beyond status/senddate and no
// payment provider has confirmed anything.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'paid') {
    $_SESSION['order_paid_demo'] = true;
    redirect('orderconfirm.php');
}

$order_result = db_query($connection, '
    SELECT o.orderid, o.orderdate, COALESCE(SUM(od.amount), 0) AS total
    FROM orders o
    LEFT JOIN orderdetails od ON od.orderid = o.orderid
    WHERE o.orderid = ? AND o.customerid = ?
    GROUP BY o.orderid, o.orderdate
', [$order_id, (int) $_SESSION['user_id']]);
$order = db_fetch_assoc($order_result);
if (!$order) {
    http_response_code(404);
    exit('Order not found.');
}
if ($payment !== 'mmqr') {
    redirect('orderconfirm.php');
}

$total = number_format((float) $order['total'], 2);
// Placeholder payload: a live integration would render the EMVCo MMQR string
// returned by the provider for this order instead of this demo text.
$qr_payload = 'MMPAY-DEMO|order=' . $order_id . '|amount=' . $total . '|currency=MMK|merchant=Book%20Palace';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MMQR Payment (Demo) - Order #<?= $order_id ?></title>
    <style>
        body { background-color: #f0f2f5; }
        .qr-frame {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 18px;
            display: inline-block;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.06);
        }
        .qr-frame svg { display: block; width: 220px; height: 220px; }
        .pay-app {
            background: #f1f3f5;
            border-radius: 999px;
            padding: 6px 14px;
            font-size: 0.85rem;
            display: inline-block;
            margin: 0 6px 6px 0;
        }
    </style>
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<body>

<?php include 'userheader.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i>MMQR Payment</h5>
                    <span class="badge bg-warning text-dark">DEMO</span>
                </div>
                <div class="card-body text-center">
                    <div class="alert alert-warning text-start" role="alert">
                        <strong>Demo mode.</strong> This QR is rendered in your browser and is not linked to a
                        payment provider. Scanning it only shows sample order text: no money moves and no card
                        or banking credentials are ever requested.
                    </div>

                    <p class="mb-1"><strong>Order ID:</strong> <code><?= $order_id ?></code></p>
                    <p class="fs-4 mb-4">Total: <strong>MMK <?= e($total) ?></strong></p>

                    <div class="qr-frame mb-4">
                        <div id="mmqr-qr" class="text-muted small">Rendering QR…</div>
                    </div>

                    <div class="mb-4">
                        <span class="pay-app">KBZPay</span>
                        <span class="pay-app">WavePay</span>
                        <span class="pay-app">AYA Pay</span>
                        <span class="pay-app">CB Pay</span>
                        <span class="pay-app">Any MMQR app</span>
                    </div>

                    <form action="orderpay.php" method="post" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="paid">
                        <button type="submit" class="btn btn-success px-4">Simulate a successful payment</button>
                    </form>
                    <a href="orderconfirm.php" class="btn btn-outline-secondary px-4">Pay later</a>

                    <p class="text-muted small mt-4 mb-0">
                        Your order is already recorded with MMQR as the chosen payment method. Connecting a
                        provider (for example MyanMyanPay) replaces this demo QR with a signed MMQR per order
                        and a verified webhook.
                    </p>
                </div>
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
            target.textContent = 'QR could not be rendered in this browser. Demo payload: ' + payload;
        }
    }());
</script>
</body>
</html>
