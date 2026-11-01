<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
require_login();
verify_csrf();
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id) { unset($_SESSION['cart'][$id]); }
redirect('cart.php');
