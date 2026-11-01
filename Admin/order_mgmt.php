<?php
session_start();
global $connection;
include 'connect.php';
$orders = mysqli_query($connection, "
    SELECT orders.*, user.email AS user_email, item_summary.items
    FROM orders
    LEFT JOIN user ON orders.customerid = user.userid
    LEFT JOIN (
        SELECT orderdetails.orderid,
               GROUP_CONCAT(CONCAT(products.productname, ' [', orderdetails.productqty, ']') SEPARATOR '||') AS items
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
            while ($out = mysqli_fetch_array($orders)) {
                if ($out['status'] > 0) {
                    echo '<tr class="table-warning">';
                } else {
                    echo '<tr>';
                }

                $orderid = $out['orderid'];
                $orderEmail = isset($out['email']) && $out['email'] !== '' ? $out['email'] : ($out['user_email'] ?? '');

                echo '<td>' . $out['orderid'] . '</td>';
                echo '<td>' . $out['deliveryname'] . '</td>';
                echo '<td>' . $out['deliveryphone'] . '</td>';
                echo '<td>' . $orderEmail . '</td>';
                echo '<td>' . $out['deliveryaddress'] . '</td>';
                echo '<td>';

                foreach (explode('||', (string) ($out['items'] ?? '')) as $item) {
                    if ($item !== '') {
                        echo '<ul class="mb-1"><li>' . htmlspecialchars($item) . '</li></ul>';
                    }
                }

                echo '</td>';
                echo '<td>' . $out['orderdate'] . '</td>';

                if ($out['status'] > 0) {
                    echo '<td>' . $out['senddate'] . '</td>';
                    echo '<td><a href="status.php?id=' . $out['orderid'] . '&status=0" class="btn btn-danger btn-sm">Undo</a></td>';
                } else {
                    echo '<td>----/--/--</td>';
                    echo '<td><a href="status.php?id=' . $out['orderid'] . '&status=1" class="btn btn-success btn-sm">Delivered</a></td>';
                }

                echo '</tr>';
            }
            ?>
        </table>
    </div>
</div>
</body>
</html>
