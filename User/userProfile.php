<?php
require_once dirname(__DIR__) . '/Admin/connect.php';

$user = require_login();
$userId = (int) $_SESSION['user_id'];

function profile_redirect(string $type, string $message): void
{
    $_SESSION['profile_notice'] = ['type' => $type, 'message' => $message];
    redirect('userProfile.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'update_profile') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $address = trim((string) ($_POST['address'] ?? ''));

        if (mb_strlen($username) < 3 || mb_strlen($username) > 100 || preg_match('/[\x00-\x1f\x7f]/', $username)) {
            profile_redirect('error', 'Your account name must be between 3 and 100 characters.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 254) {
            profile_redirect('error', 'Please enter a valid email address.');
        }
        if (mb_strlen($phone) > 30 || mb_strlen($address) > 1000) {
            profile_redirect('error', 'Please check your phone number and delivery address.');
        }

        try {
            db_execute($connection, 'UPDATE users SET username = ?, email = ?, phone = ?, address = ? WHERE userid = ?', [$username, $email, $phone, $address, $userId]);
        } catch (PDOException $error) {
            if ($error->getCode() === '23505') {
                profile_redirect('error', 'That account name or email is already in use.');
            }
            throw $error;
        }

        $_SESSION['user'] = $username;
        profile_redirect('success', 'Your account details have been updated.');
    }

    if ($action === 'update_password') {
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
        $login = db_fetch_assoc(db_query($connection, 'SELECT * FROM login_record(?)', [$user['username']]));
        $storedHash = (string) ($login['password'] ?? '');

        if ($storedHash === '' || !password_verify($currentPassword, $storedHash)) {
            profile_redirect('error', 'Your current password is incorrect.');
        }
        if (strlen($newPassword) < 12 || strlen($newPassword) > 72) {
            profile_redirect('error', 'Your new password must be between 12 and 72 characters.');
        }
        if ($newPassword !== $confirmPassword) {
            profile_redirect('error', 'The new passwords do not match.');
        }
        if (password_verify($newPassword, $storedHash)) {
            profile_redirect('error', 'Choose a new password that is different from your current password.');
        }

        db_execute($connection, 'UPDATE users SET password = ? WHERE userid = ?', [password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
        profile_redirect('success', 'Your password has been changed successfully.');
    }

    profile_redirect('error', 'That account action is not available.');
}

$user = current_user_record() ?? $user;
$orders = [];
$orderResult = db_query($connection, '
    SELECT o.orderid, o.deliveryname, o.deliveryaddress, o.orderdate, o.status, o.senddate,
           COALESCE(SUM(od.productqty), 0) AS item_count,
           COALESCE(SUM(od.amount), 0) AS total_amount
    FROM orders o
    LEFT JOIN orderdetails od ON od.orderid = o.orderid
    WHERE o.customerid = ?
    GROUP BY o.orderid, o.deliveryname, o.deliveryaddress, o.orderdate, o.status, o.senddate
    ORDER BY o.orderid DESC
', [$userId]);
while ($order = db_fetch_assoc($orderResult)) {
    $order['items'] = [];
    $orders[(int) $order['orderid']] = $order;
}

if ($orders) {
    $itemResult = db_query($connection, '
        SELECT od.orderid, od.productqty, od.unit_price, p.productname
        FROM orderdetails od
        JOIN orders o ON o.orderid = od.orderid
        JOIN products p ON p.productid = od.productid
        WHERE o.customerid = ?
        ORDER BY od.orderid DESC, p.productname
    ', [$userId]);
    while ($item = db_fetch_assoc($itemResult)) {
        $orderId = (int) $item['orderid'];
        if (isset($orders[$orderId])) {
            $orders[$orderId]['items'][] = $item;
        }
    }
}

$notice = $_SESSION['profile_notice'] ?? null;
unset($_SESSION['profile_notice']);
$deliveredCount = count(array_filter($orders, static fn (array $order): bool => (int) $order['status'] === 1));
$pendingCount = count($orders) - $deliveredCount;
$initial = mb_strtoupper(mb_substr((string) $user['username'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | Book Palace</title>
    <link rel="stylesheet" href="../assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="profile.css?v=20261102-logout">
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
    <script defer src="../assets/js/auth-form.js?v=20261102-profile"></script>
</head>
<body>
<?php include __DIR__ . '/userheader.php'; ?>

<main class="account-page">
    <div class="account-shell">
        <header class="account-heading">
            <div>
                <p class="account-eyebrow">My account</p>
                <h1>Welcome, <?php echo e($user['username']); ?></h1>
                <p>Manage your details, keep your password secure, and follow your orders.</p>
            </div>
            <div class="account-heading__actions">
                <a class="account-shop-link" href="userindex.php">Continue shopping</a>
                <form action="logout.php" method="post" class="account-logout-form">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="account-logout-button">Log out</button>
                </form>
            </div>
        </header>

        <?php if (is_array($notice)): ?>
            <div class="account-notice account-notice--<?php echo $notice['type'] === 'success' ? 'success' : 'error'; ?>" role="status"><?php echo e($notice['message']); ?></div>
        <?php endif; ?>

        <div class="account-summary" aria-label="Account summary">
            <section class="account-identity"><span class="account-avatar" aria-hidden="true"><?php echo e($initial); ?></span><div><strong><?php echo e($user['username']); ?></strong><span><?php echo e($user['email']); ?></span></div></section>
            <section><strong><?php echo count($orders); ?></strong><span>Total orders</span></section>
            <section><strong><?php echo $pendingCount; ?></strong><span>In progress</span></section>
            <section><strong><?php echo $deliveredCount; ?></strong><span>Delivered</span></section>
        </div>

        <div class="account-grid">
            <section class="account-panel" aria-labelledby="details-title">
                <div class="account-panel__heading"><h2 id="details-title">Account details</h2><p>These details are used when you place an order.</p></div>
                <form action="userProfile.php" method="post" class="account-form">
                    <?php echo csrf_field(); ?><input type="hidden" name="action" value="update_profile">
                    <div class="account-field"><label for="username">Account name</label><input type="text" id="username" name="username" value="<?php echo e($user['username']); ?>" minlength="3" maxlength="100" autocomplete="username" required></div>
                    <div class="account-field"><label for="email">Email address</label><input type="email" id="email" name="email" value="<?php echo e($user['email']); ?>" maxlength="254" autocomplete="email" required></div>
                    <div class="account-form__row">
                        <div class="account-field"><label for="phone">Phone number</label><input type="tel" id="phone" name="phone" value="<?php echo e($user['phone']); ?>" maxlength="30" autocomplete="tel"></div>
                        <div class="account-field"><label for="address">Delivery address</label><input type="text" id="address" name="address" value="<?php echo e($user['address']); ?>" maxlength="1000" autocomplete="street-address"></div>
                    </div>
                    <button type="submit" class="account-button">Save account details</button>
                </form>
            </section>

            <section class="account-panel" aria-labelledby="password-title">
                <div class="account-panel__heading"><h2 id="password-title">Change password</h2><p>Use 12 or more characters for a stronger password.</p></div>
                <form action="userProfile.php" method="post" class="account-form">
                    <?php echo csrf_field(); ?><input type="hidden" name="action" value="update_password">
                    <?php foreach ([['current_password', 'Current password'], ['new_password', 'New password'], ['confirm_password', 'Confirm new password']] as [$fieldId, $fieldLabel]): ?>
                        <div class="account-field">
                            <label for="<?php echo e($fieldId); ?>"><?php echo e($fieldLabel); ?></label>
                            <div class="account-password">
                                <input type="password" id="<?php echo e($fieldId); ?>" name="<?php echo e($fieldId); ?>" minlength="<?php echo $fieldId === 'current_password' ? '1' : '12'; ?>" maxlength="72" autocomplete="<?php echo $fieldId === 'current_password' ? 'current-password' : 'new-password'; ?>" required>
                                <button type="button" data-password-toggle="<?php echo e($fieldId); ?>" aria-label="Show password" aria-pressed="false">Show</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" class="account-button">Update password</button>
                </form>
            </section>
        </div>

        <section class="orders-section" id="order-tracking" aria-labelledby="orders-title">
            <div class="orders-heading"><div><p class="account-eyebrow">Order tracking</p><h2 id="orders-title">Your orders</h2></div><p>Status updates are shown here after the store processes your order.</p></div>
            <?php if (!$orders): ?>
                <div class="orders-empty"><h3>No orders yet</h3><p>Once you place an order, its delivery status and items will appear here.</p><a href="userindex.php" class="account-button">Browse books</a></div>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order):
                        $isDelivered = (int) $order['status'] === 1;
                        $orderDate = date('M j, Y', strtotime((string) $order['orderdate']));
                        $sendDate = $order['senddate'] ? date('M j, Y', strtotime((string) $order['senddate'])) : null;
                    ?>
                        <article class="order-card" id="order-<?php echo e($order['orderid']); ?>">
                            <header class="order-card__header">
                                <div><span>Order number</span><strong>#<?php echo e($order['orderid']); ?></strong></div>
                                <div><span>Placed on</span><strong><?php echo e($orderDate); ?></strong></div>
                                <div><span>Total</span><strong><?php echo e(number_format((float) $order['total_amount'], 2)); ?> MMK</strong></div>
                                <span class="order-badge order-badge--<?php echo $isDelivered ? 'delivered' : 'pending'; ?>"><?php echo $isDelivered ? 'Delivered' : 'Processing'; ?></span>
                            </header>
                            <div class="order-card__body">
                                <div class="order-items">
                                    <h3><?php echo e($order['item_count']); ?> item<?php echo (int) $order['item_count'] === 1 ? '' : 's'; ?></h3>
                                    <ul><?php foreach ($order['items'] as $item): ?><li><span><?php echo e($item['productname']); ?></span><span><?php echo e($item['productqty']); ?> × <?php echo e(number_format((float) $item['unit_price'], 2)); ?> MMK</span></li><?php endforeach; ?></ul>
                                    <p class="order-address"><strong>Delivery to:</strong> <?php echo e($order['deliveryname']); ?>, <?php echo e($order['deliveryaddress']); ?></p>
                                </div>
                                <div class="order-progress" aria-label="Order progress">
                                    <div class="order-progress__step is-complete"><span></span><div><strong>Order placed</strong><small><?php echo e($orderDate); ?></small></div></div>
                                    <div class="order-progress__step <?php echo $isDelivered ? 'is-complete' : 'is-current'; ?>"><span></span><div><strong><?php echo $isDelivered ? 'Processed' : 'Processing'; ?></strong><small><?php echo $isDelivered ? 'Prepared by Book Palace' : 'We are preparing your order'; ?></small></div></div>
                                    <div class="order-progress__step <?php echo $isDelivered ? 'is-complete' : ''; ?>"><span></span><div><strong>Delivered</strong><small><?php echo $sendDate ? e($sendDate) : 'Waiting for delivery'; ?></small></div></div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>
</body>
</html>
