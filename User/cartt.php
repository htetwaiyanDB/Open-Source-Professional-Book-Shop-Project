<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
require_login();



require_once __DIR__ . '/userfunction.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shopping Cart</title>
<style>
    
</style>
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<body>
<?php include 'userheader.php'; echo render_flash(); ?>
<div class="container">
<div class="row mt-5">
<div class="col-sm-12">
<div class="card">
<div class="card-header">
<h3 style="border:none,">Welcome ,
<span class="showname text-danger">
<?php
                            if (!empty($_SESSION['user'])) {
                                echo e($_SESSION['user']);
                            } else {
                                $_SESSION['user'] = '';
                                echo " ";
                            }
                            ?>
</span>
</h3>
</div>
<div class="row">
<div class="col-md-12">
<div class="card">
<div class="card-header text-info text-center">
<h2>Shopping Cart</h2>
</div>
<?php if (!empty($_SESSION['cart'])) : ?>
    <div class="card-body">
        <table class="table table-condensed">
    <thead>
        <tr>
            <th>Photo</th>
            <th>Name</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Price</th>
            <th>Action</th>
        </tr>
    </thead>
<body>
<?php
         $total = 0;
         foreach ($_SESSION['cart'] as $id => $qty) {
         $id = (int) $id;
                                                    $qty = max(1, (int) $qty);
         $result = db_query($connection, 'SELECT * FROM products WHERE productid = ?', [$id]);
 
         if ($result && db_num_rows($result) > 0) {
         $row = db_fetch_assoc($result);
?>
        <tr>
            <td><img src="../Photos/<?php echo e($row['photo']); ?>" width="100" height="100" class="img img-thumbnail" /></td>
            <td><?php echo e($row['productname']); ?></td>
            <td>
        
            <span>
            <form action="change-qty.php" method="post" style="display:inline"><?php echo csrf_field(); ?><input type="hidden" name="id" value="<?php echo (int) $id; ?>"><input type="hidden" name="action" value="increase"><button type="submit" class="btn btn-outline-primary btn-sm">+</button></form>
            <?php echo e($qty); ?>
            <form action="change-qty.php" method="post" style="display:inline"><?php echo csrf_field(); ?><input type="hidden" name="id" value="<?php echo (int) $id; ?>"><input type="hidden" name="action" value="decrease"><button type="submit" class="btn btn-outline-danger btn-sm">-</button></form>
            </span>
            </td>
            <td>MMK-<?php echo e($row['price']); ?></td>
            <td>MMK-<?php echo $row['price'] * $qty ?></td>
            <td>
            <form action="remove.php" method="post" style="display:inline"><?php echo csrf_field(); ?><input type="hidden" name="id" value="<?php echo (int) $id; ?>"><button type="submit" class="btn btn-outline-danger btn-sm">Remove</button></form>
            </td>
        </tr>
<?php
                                $total += $row['price'] * $qty;
                                } else {
                                echo 'This book is no longer available.';
                                    }
                                }
?>
</tbody>

<tfoot>
    <tr>
        <td colspan="4" align="right"><b>Total:</b></td>
        <td>MMK-<?php echo e($total); ?></td>
        <td></td>
    </tr>
</tfoot>
</table>
</div>
<div class="card-footer">
<form action="clear.php" method="post" style="display:inline"><?php echo csrf_field(); ?><button type="submit" class="btn btn-danger">Clear Cart</button></form>
<a href="userindex.php" class="btn btn-dark">Back</a>
<a href="submitorder.php" class="btn btn-primary">Submit Order</a>
</div>
<?php else : ?>
    <div class="card-body">
    <h3 class="text-danger lead text-center">You have not selected any products yet!</h3>
    <p class="text-center"><a href="userindex.php" class="btn btn-info">Shop Now</a></p>
    </div>
<?php endif; ?>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</body>
</html>
