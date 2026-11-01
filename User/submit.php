<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
require_login();
verify_csrf();
// Identity comes from the server session, prices from locked database rows.
// Posted customer IDs, prices, totals and card data are never trusted or stored.
$cart = $_SESSION['cart'] ?? [];
if (!is_array($cart) || $cart === [] || count($cart) > 100) {
    flash_error('Your cart is empty or exceeds the 100-book limit.'); redirect('cart.php');
}
$items = [];
foreach ($cart as $id => $quantity) {
    if (!ctype_digit((string) $id) || !ctype_digit((string) $quantity) || (int) $id < 1 || (int) $quantity < 1 || (int) $quantity > 999) {
        flash_error('Invalid cart quantities. Please update your cart.'); redirect('cart.php');
    }
    $items[] = ['productid' => (int) $id, 'qty' => (int) $quantity];
}
$name = trim((string) ($_POST['username'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$address = trim((string) ($_POST['address'] ?? ''));
if ($name === '' || $phone === '' || $address === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_error('Please enter complete delivery details and a valid email.'); redirect('submitorder.php');
}
try {
    $id = db_scalar($connection, 'SELECT checkout(?::jsonb, ?, ?, ?, ?)', [json_encode($items, JSON_THROW_ON_ERROR), $name, $email, $phone, $address]);
} catch (PDOException $error) {
    if (!in_array($error->getCode(), ['P0001', '23514', '22023', '40001', '40P01', '55P03'], true)) { throw $error; }
    flash_error('The order could not be placed. Check stock and delivery details, then try again.'); redirect('cart.php');
}
$_SESSION['order_id'] = (int) $id;
unset($_SESSION['cart']);
redirect('orderconfirm.php');
