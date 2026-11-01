<?php
require_once __DIR__ . '/connect.php';
require_admin();
require_once __DIR__ . '/function.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    del_product();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books List</title>
    <link rel="stylesheet" href="../assets/vendor/bootstrap/bootstrap.min.css">
    <script defer src="../assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<body class="bg-light">
<?php include 'header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Books List</h2>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="product.php" class="btn btn-outline-info">Add Book</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover bg-white">
            <tr>
                <th>No</th>
                <th>Photo</th>
                <th>Book Name</th>
                <th>Author Name</th>
                <th>Category Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Action</th>
            </tr>
            <?php
            $products = db_query($connection, 'SELECT products.productid, products.productname, products.description, products.price, products.qty, products.photo, categorys.catname, author.authorname
                         FROM products
                         JOIN categorys ON products.categoryid = categorys.catid
                         JOIN author ON products.authorid = author.authorid
                         ORDER BY products.productid DESC');
            while ($row = db_fetch_assoc($products)) {
                $productid = (int) $row['productid'];
                $productname = e($row['productname']);
                $authorname = e($row['authorname']);
                $catname = e($row['catname']);
                $desc = e($row['description']);
                $price = e($row['price']);
                $qty = (int) $row['qty'];
                $photo = e(rawurlencode($row['photo']));
                echo "<tr>";
                echo "<td>{$productid}</td>";
                echo "<td><img src='../Photos/{$photo}' width='60' alt='' loading='lazy' decoding='async'></td>";
                echo "<td>{$productname}</td>";
                echo "<td>{$authorname}</td>";
                echo "<td>{$catname}</td>";
                echo "<td>{$desc}</td>";
                echo "<td>{$price}</td>";
                echo "<td>{$qty}</td>";
                echo "<td>
                        <a href='edit.php?action=edit&pid={$productid}' class='btn btn-outline-success btn-sm'>Edit</a>
                        <form action='productlist.php' method='post' class='d-inline' onsubmit=\"return confirm('Are you sure?')\">" . csrf_field() . "<input type='hidden' name='action' value='delete'><input type='hidden' name='pid' value='{$productid}'><button type='submit' class='btn btn-danger btn-sm'>Delete</button></form>
                      </td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</div>
</body>
</html>
