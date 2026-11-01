<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
require_login();
verify_csrf();
unset($_SESSION['cart']);
redirect('cart.php');
