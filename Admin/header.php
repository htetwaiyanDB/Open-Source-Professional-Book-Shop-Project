<?php
require_once __DIR__ . '/connect.php';
require_admin();
$adminPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center fw-bold" href="dashboard.php">
            <img src="../Photos/logo.png" alt="Book Palace" style="width: 60px; height: 44px; object-fit: contain; margin-right: 10px;">
            <span>Book Palace Admin</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo $adminPage === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo in_array($adminPage, ['product.php', 'productlist.php', 'edit.php']) ? 'active' : ''; ?>" href="productlist.php">Books</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo in_array($adminPage, ['user.php', 'userlist.php']) ? 'active' : ''; ?>" href="userlist.php">Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $adminPage === 'order_mgmt.php' ? 'active' : ''; ?>" href="order_mgmt.php">Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $adminPage === 'feedback.php' ? 'active' : ''; ?>" href="feedback.php">Feedback</a>
                </li>
                <li class="nav-item ms-lg-2">
                    <form action="logout.php" method="post" class="d-inline"><?php echo csrf_field(); ?><button type="submit" class="btn btn-outline-dark btn-sm mt-1 mt-lg-0">Logout</button></form>
                </li>
            </ul>
        </div>
    </div>
</nav>
