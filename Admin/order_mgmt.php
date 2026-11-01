<?php
require_once __DIR__ . '/connect.php';
require_admin();

$orders = db_query($connection, "
    SELECT orders.*, users.email AS user_email, item_summary.items
    FROM orders
    LEFT JOIN users ON orders.customerid = users.userid
    LEFT JOIN (
        SELECT orderdetails.orderid,
               string_agg(products.productname || ' [' || orderdetails.productqty || ']', '||') AS items
        FROM orderdetails
        LEFT JOIN products ON orderdetails.productid = products.productid
        GROUP BY orderdetails.orderid
    ) AS item_summary ON orders.orderid = item_summary.orderid
    ORDER BY orders.orderid DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
    <link rel="stylesheet" href="../assets/vendor/bootstrap/bootstrap.min.css">
    <script defer src="../assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<body class="bg-light">
<?php include 'header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Welcome Admin,
                <span class="text-danger">
                    <?php
                    if (isset($_SESSION['admin'])) {
                        echo htmlspecialchars($_SESSION['admin']);
                    } else {
                        $_SESSION['admin'] = '';
                    }
                    ?>
                </span>
            </h2>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover bg-white">
            <tr>
                <td>No</td>
                <td>Customer Name</td>
                <td>Customer Phone</td>
                <td>Email</td>
                <td>Customer Address</td>
                <td>Item(s)</td>
                <td>Ordered_Date</td>
                <td>Sended_Date</td>
                <td>Action</td>
            </tr>
            <?php
            while ($out = db_fetch_assoc($orders)) {
                if ($out['status'] > 0) {
                    echo '<tr class="table-warning">';
                } else {
                    echo '<tr>';
                }

                $orderid = (int) $out['orderid'];
                $orderEmail = isset($out['email']) && $out['email'] !== '' ? $out['email'] : ($out['user_email'] ?? '');

                echo '<td>' . $orderid . '</td>';
                echo '<td>' . e($out['deliveryname']) . '</td>';
                echo '<td>' . e($out['deliveryphone']) . '</td>';
                echo '<td>' . e($orderEmail) . '</td>';
                echo '<td>' . e($out['deliveryaddress']) . '</td>';
                echo '<td>';

                foreach (explode('||', (string) ($out['items'] ?? '')) as $item) {
                    if ($item !== '') {
                        echo '<ul class="mb-1"><li>' . e($item) . '</li></ul>';
                    }
                }

                echo '</td>';
                echo '<td>' . e($out['orderdate']) . '</td>';

                if ($out['status'] > 0) {
                    echo '<td>' . e($out['senddate']) . '</td>';
                    echo '<td><form action="status.php" method="post" class="d-inline">' . csrf_field() . '<input type="hidden" name="id" value="' . $orderid . '"><input type="hidden" name="status" value="0"><button type="submit" class="btn btn-danger btn-sm">Undo</button></form></td>';
                } else {
                    echo '<td>----/--/--</td>';
                    echo '<td><form action="status.php" method="post" class="d-inline">' . csrf_field() . '<input type="hidden" name="id" value="' . $orderid . '"><input type="hidden" name="status" value="1"><button type="submit" class="btn btn-success btn-sm">Delivered</button></form></td>';
                }

                echo '</tr>';
            }
            ?>
        </table>
    </div>
</div>
</body>
</html>
