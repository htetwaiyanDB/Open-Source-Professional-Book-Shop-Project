<?php
session_start();
include 'connect.php';
include 'function.php';
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
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
            $go_query = "SELECT products.*, categorys.*, author.authorname
                         FROM products
                         JOIN categorys ON products.categoryid = categorys.catid
                         JOIN author ON products.authorid = author.authorid
                         ORDER BY products.productid DESC";
            $query = mysqli_query($connection, $go_query);
            while ($row = mysqli_fetch_array($query)) {
                $productid = $row['productid'];
                $productname = $row['productname'];
                $authorname = $row['authorname'];
                $catname = $row['catname'];
                $desc = $row['description'];
                $price = $row['price'];
                $qty = $row['Qty'];
                $photo = $row['photo'];
                echo "<tr>";
                echo "<td>{$productid}</td>";
                echo "<td><img src='../photos/{$photo}' width='60' alt='' loading='lazy' decoding='async'></td>";
                echo "<td>{$productname}</td>";
                echo "<td>{$authorname}</td>";
                echo "<td>{$catname}</td>";
                echo "<td>{$desc}</td>";
                echo "<td>{$price}</td>";
                echo "<td>{$qty}</td>";
                echo "<td>
                        <a href='edit.php?action=edit&pid={$productid}' class='btn btn-outline-success btn-sm'>Edit</a>
                        <a href='productlist.php?action=delete&pid={$productid}' class='btn btn-danger btn-sm' onclick=\"return confirm('Are you sure?')\">Delete</a>
                      </td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</div>
</body>
</html>
