<?php
require_once __DIR__ . '/connect.php';
require_admin();
verify_csrf();
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$status = (string) ($_POST['status'] ?? '');
if (!$id || !in_array($status, ['0', '1'], true)) {
    http_response_code(400);
    exit('Invalid order status request.');
}
if ($status === '1') {
    db_execute($connection, 'UPDATE orders SET status = 1, senddate = CURRENT_TIMESTAMP WHERE orderid = ?', [$id]);
} else {
    db_execute($connection, 'UPDATE orders SET status = 0, senddate = NULL WHERE orderid = ?', [$id]);
}
redirect('order_mgmt.php');
