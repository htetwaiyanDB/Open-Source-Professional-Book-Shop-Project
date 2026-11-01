<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
require_login();
verify_csrf();
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$action = $_POST['action'] ?? '';
if ($id && isset($_SESSION['cart'][$id])) {
    $qty = (int) $_SESSION['cart'][$id];
    if ($action === 'increase') {
        $stock = db_scalar($connection, 'SELECT qty FROM products WHERE productid = ?', [$id]);
        if ($stock !== null && $qty < 999 && $qty < (int) $stock) {
            $_SESSION['cart'][$id] = $qty + 1;
        } else {
            flash_error('The requested quantity exceeds available stock.');
        }
    } elseif ($action === 'decrease') {
        if ($qty > 1) {
            $_SESSION['cart'][$id] = $qty - 1;
        } else {
            unset($_SESSION['cart'][$id]);
        }
    }
}
redirect('cart.php');
