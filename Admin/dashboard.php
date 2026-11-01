<?php
require_once __DIR__ . '/connect.php';
require_admin();

$counts = db_fetch_assoc(db_query($connection, "
    SELECT
        (SELECT COUNT(*) FROM categorys) AS category_count,
        (SELECT COUNT(*) FROM author) AS author_count,
        (SELECT COUNT(*) FROM users) AS user_count,
        (SELECT COUNT(*) FROM products) AS book_count,
        (SELECT COUNT(*) FROM orders) AS order_count,
        (SELECT COUNT(*) FROM feedback) AS feedback_count
"));
$categoryCount = (int) $counts['category_count'];
$authorCount = (int) $counts['author_count'];
$userCount = (int) $counts['user_count'];
$bookCount = (int) $counts['book_count'];
$orderCount = (int) $counts['order_count'];
$feedbackCount = (int) $counts['feedback_count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
            <h2>
                Welcome Admin,
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
            <p class="text-muted mb-0">Manage books, users, orders, and feedback from the admin panel.</p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Categories</h5>
                    <h3 class="text-info"><?php echo $categoryCount; ?></h3>
                    <a href="category.php" class="btn btn-outline-info btn-sm">Manage Categories</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Authors</h5>
                    <h3 class="text-info"><?php echo $authorCount; ?></h3>
                    <a href="author.php" class="btn btn-outline-info btn-sm">Manage Authors</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
                    <h3 class="text-info"><?php echo $userCount; ?></h3>
                    <a href="userlist.php" class="btn btn-outline-info btn-sm">View Users</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Books</h5>
                    <h3 class="text-info"><?php echo $bookCount; ?></h3>
                    <a href="productlist.php" class="btn btn-outline-info btn-sm">View Books</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Orders</h5>
                    <h3 class="text-info"><?php echo $orderCount; ?></h3>
                    <a href="order_mgmt.php" class="btn btn-outline-info btn-sm">Manage Orders</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Feedback</h5>
                    <h3 class="text-info"><?php echo $feedbackCount; ?></h3>
                    <a href="feedback.php" class="btn btn-outline-info btn-sm">View Feedback</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
