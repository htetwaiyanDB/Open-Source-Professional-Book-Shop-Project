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
    body {
        background-color: #f0f2f5;
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    }
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .card-header-custom {
        background-color: #fff;
        border-bottom: 1px solid #dee2e6;
        padding: 1.5rem;
    }
    .card-body-custom {
        background-color: #fff;
        padding: 2rem;
    }
    .card-footer-custom {
        background-color: #fff;
        border-top: 1px solid #dee2e6;
        padding: 1.5rem;
    }
    .btn-custom {
        margin: 0.5rem;
        border-radius: 8px;
    }
    .product-img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .table thead th {
        border-bottom: 2px solid #dee2e6;
    }
    .table tfoot td {
        border-top: 2px solid #dee2e6;
    }
    .showname {
        font-size: 1.25rem;
        font-weight: bold;
    }
</style>
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<body>
<?php include 'userheader.php'; echo render_flash(); ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
           
              
                <div class="card-body card-body-custom">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-3">
                                <div class="card-header text-center bg-info text-white">
                                    <h2>Shopping Cart</h2>
                                </div>
                                <?php if (!empty($_SESSION['cart'])) : ?>
                                    <div class="card-body">
                                        <table class="table table-hover">
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
                                            <tbody>
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
                                                            <td><img src="Photos/<?php echo e($row['photo']); ?>" class="img-thumbnail product-img" /></td>
                                                            <td><?php echo e($row['productname']); ?></td>
                                                            <td>
                                                                <span>
                                                                    <form action="change-qty.php" method="post" style="display:inline"><?php echo csrf_field(); ?><input type="hidden" name="id" value="<?php echo (int) $id; ?>"><input type="hidden" name="action" value="increase"><button type="submit" class="btn btn-outline-success btn-sm"><i class="fas fa-plus"></i></button></form>
                                                                    <?php echo e($qty); ?>
                                                                    <form action="change-qty.php" method="post" style="display:inline"><?php echo csrf_field(); ?><input type="hidden" name="id" value="<?php echo (int) $id; ?>"><input type="hidden" name="action" value="decrease"><button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-minus"></i></button></form>
                                                                </span>
                                                            </td>
                                                            <td>MMK-<?php echo number_format($row['price'], 2) ?></td>
                                                            <td>MMK-<?php echo number_format($row['price'] * $qty, 2) ?></td>
                                                            <td>
                                                                <form action="remove.php" method="post" style="display:inline"><?php echo csrf_field(); ?><input type="hidden" name="id" value="<?php echo (int) $id; ?>"><button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i> Remove</button></form>
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
                                                    <td colspan="4" class="text-end"><b>Total:</b></td>
                                                    <td>MMK-<?php echo number_format($total, 2); ?></td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="card-footer card-footer-custom text-center">
                                        <form action="clear.php" method="post" style="display:inline"><?php echo csrf_field(); ?><button type="submit" class="btn btn-danger btn-custom">Clear Cart</button></form>
                                        <a href="userindex.php" class="btn btn-secondary btn-custom">Back</a>
                                        <a href="submitorder.php" class="btn btn-primary btn-custom">Submit Order</a>
                                    </div>
                                <?php else : ?>
                                    <div class="card-body text-center">
                                        <h3 class="text-danger lead">You have not selected any products yet!</h3>
                                        <p><a href="userindex.php" class="btn btn-info btn-custom">Shop Now</a></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
