<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
require_login();
verify_csrf();
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$product = $id ? db_fetch_assoc(db_query($connection, 'SELECT productid, qty FROM products WHERE productid = ?', [$id])) : false;
if ((int) ($_SESSION['cart'][$id] ?? 0) >= 999 || !$product || (int) $product['qty'] <= (int) ($_SESSION['cart'][$id] ?? 0)) {
    flash_error('This book is unavailable or the requested quantity exceeds stock.');
    redirect('cart.php');
}
$_SESSION['cart'][$id] = (int) ($_SESSION['cart'][$id] ?? 0) + 1;
redirect('cart.php');
