<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
require_login();

// The demo MMQR code is now shown as a popup on the order confirmation page, so
// this file only keeps old links, bookmarks and browser history working.
if (empty($_SESSION['order_id'])) {
    flash_error('Place an order before opening the payment page.');
    redirect('cart.php');
}

redirect(($_SESSION['order_payment'] ?? 'cod') === 'mmqr'
    ? 'orderconfirm.php?pay=1'
    : 'orderconfirm.php');
