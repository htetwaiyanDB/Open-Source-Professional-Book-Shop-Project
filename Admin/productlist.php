<?php
require_once __DIR__ . '/connect.php';
require_admin();
require_once __DIR__ . '/function.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    del_product();
}

$perPage = 10;
$pageValue = $_GET['page'] ?? '1';
$page = is_scalar($pageValue) && ctype_digit((string) $pageValue) && (int) $pageValue > 0 ? (int) $pageValue : 1;
$totalProducts = (int) db_scalar($connection, 'SELECT COUNT(*) FROM products');
$totalPages = max(1, (int) ceil($totalProducts / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$showingFrom = $totalProducts === 0 ? 0 : $offset + 1;
$showingTo = min($offset + $perPage, $totalProducts);

$products = db_query($connection, 'SELECT products.productid, products.productname, products.description, products.price, products.qty, products.photo, categorys.catname, author.authorname
                     FROM products
                     JOIN categorys ON products.categoryid = categorys.catid
                     JOIN author ON products.authorid = author.authorid
                     ORDER BY products.productid DESC
                     LIMIT ? OFFSET ?', [$perPage, $offset]);

$visiblePages = [];
for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++) {
    if ($pageNumber === 1 || $pageNumber === $totalPages || abs($pageNumber - $page) <= 2) {
        $visiblePages[] = $pageNumber;
    }
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
            <thead><tr>
                <th>No</th>
                <th>Photo</th>
                <th>Book Name</th>
                <th>Author Name</th>
                <th>Category Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Action</th>
            </tr></thead>
            <tbody>
            <?php
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
                        <form action='productlist.php' method='post' class='d-inline' onsubmit=\"return confirm('Are you sure?')\">" . csrf_field() . "<input type='hidden' name='action' value='delete'><input type='hidden' name='pid' value='{$productid}'><input type='hidden' name='page' value='{$page}'><button type='submit' class='btn btn-danger btn-sm'>Delete</button></form>
                      </td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

    <div class="admin-pagination-wrap">
        <p class="admin-pagination__summary">Showing <?php echo $showingFrom; ?>–<?php echo $showingTo; ?> of <?php echo $totalProducts; ?> books</p>
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Books pagination">
                <ul class="admin-pagination">
                    <li><?php if ($page > 1): ?><a class="admin-pagination__link" href="productlist.php?page=<?php echo $page - 1; ?>" rel="prev">Previous</a><?php else: ?><span class="admin-pagination__link is-disabled" aria-disabled="true">Previous</span><?php endif; ?></li>
                    <?php $lastRenderedPage = 0; foreach ($visiblePages as $visiblePage): ?>
                        <?php if ($lastRenderedPage > 0 && $visiblePage > $lastRenderedPage + 1): ?><li><span class="admin-pagination__ellipsis" aria-hidden="true">…</span></li><?php endif; ?>
                        <li><a class="admin-pagination__link <?php echo $visiblePage === $page ? 'is-current' : ''; ?>" href="productlist.php?page=<?php echo $visiblePage; ?>" <?php echo $visiblePage === $page ? 'aria-current="page"' : ''; ?>><?php echo $visiblePage; ?></a></li>
                        <?php $lastRenderedPage = $visiblePage; ?>
                    <?php endforeach; ?>
                    <li><?php if ($page < $totalPages): ?><a class="admin-pagination__link" href="productlist.php?page=<?php echo $page + 1; ?>" rel="next">Next</a><?php else: ?><span class="admin-pagination__link is-disabled" aria-disabled="true">Next</span><?php endif; ?></li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
