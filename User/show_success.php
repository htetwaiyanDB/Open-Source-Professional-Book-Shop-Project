<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
require_login();

 

 
// Check if order_id is set in session
if (isset($_SESSION['order_id'])) {
    $order_id = (int) $_SESSION['order_id']; 
} else {
    die("Order ID not found in session.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Confirmation</title>
<link rel="stylesheet" href="../assets/vendor/bootstrap/bootstrap.min.css">
<link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<body>
<!--<?php include 'userheader.php' ?> -->
<div class="container mt-5">
<div class="row">
<div class="col-md-6">
<?php
                $query = "select * from orders where orderid = ? AND customerid = ?";
                $go_query = db_query($connection, $query, [$order_id, (int) $_SESSION['user_id']]);
                if (db_num_rows($go_query) === 0) { http_response_code(404); exit('Order not found.'); }
                while ($out = db_fetch_array($go_query)) {
                    $db_name = $out['deliveryname'];
                    $db_phone = $out['deliveryphone'];
                    $db_address = $out['deliveryaddress'];
                    $db_email = $out['email'];
                }
            ?>
<div class="card">
<div class="card-header bg-primary text-white">
<h4 class="card-title">Customer Information</h4>
</div>
<div class="card-body">
<p class="card-text"><strong>Customer Name:</strong> <?php echo e($db_name); ?></p>
<p class="card-text"><strong>Customer Phone:</strong> <?php echo e($db_phone); ?></p>
<p class="card-text"><strong>Customer Address:</strong> <?php echo e($db_address);?></p>
<p class="card-text"><strong>Customer Email:</strong> <?php echo e($db_email);?></p>
</div>
</div>
</div>
<div class="col-md-6">
<table class="table table-striped">
<thead>
<tr>
<th colspan="4" class="bg-info text-white">Order Information</th>
</tr>
<tr>
<th>Product Name</th>
<th>Product Price</th>
<th>Product Qty</th>
<th>Unit Price</th>
</tr>
</thead>
<tbody>
<?php 
                    $total = 0;
                    $query = 'SELECT od.productqty, od.unit_price AS price, od.amount, p.productname FROM orderdetails od JOIN orders o ON o.orderid = od.orderid LEFT JOIN products p ON od.productid = p.productid WHERE od.orderid = ? AND o.customerid = ?';
                    $go_query = db_query($connection, $query, [$order_id, (int) $_SESSION['user_id']]);
                    while ($out = db_fetch_array($go_query)) {
                        $product_name = $out['productname'];
                        $price = $out['price'];
                        $qty = $out['productqty'];
                        $unit_price = $out['amount'];
                        $total += $unit_price;
                        echo "<tr>
<td>" . e($product_name) . "<br></td>
<td>" . e($price) . "<br></td>
<td>" . e($qty) . "<br></td>
<td>" . e($unit_price) . "</td>
</tr>";
                    }
                    ?>
<tr>
<td colspan="3" align="right"><strong>Total Amount:</strong></td>
<td><?php echo e($total); ?></td>
</tr>
</tbody>
</table>
</div>
</div>
</div>
 
<script defer src="../assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
