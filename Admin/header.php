<?php
require_once __DIR__ . '/connect.php';
require_admin();
$adminPage = basename($_SERVER['PHP_SELF']);
$catalogActive = in_array($adminPage, ['category.php', 'author.php'], true);
?>
<link rel="stylesheet" href="admin-ui.css?v=20261102-pagination">
<header class="admin-topbar">
<nav class="navbar navbar-expand-xl" aria-label="Admin navigation">
    <div class="admin-topbar__inner">
        <a class="admin-brand" href="dashboard.php">
            <img src="../Photos/logo.png" alt="">
            <span><strong>Book Palace</strong><small>Administration</small></span>
        </a>
        <button class="navbar-toggler admin-nav-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav admin-nav-list">
                <li class="nav-item">
                    <a class="admin-nav-link <?php echo $adminPage === 'dashboard.php' ? 'is-active' : ''; ?>" href="dashboard.php"><svg aria-hidden="true"><use href="../User/auth-icons.svg#dashboard"></use></svg>Overview</a>
                </li>
                <li class="nav-item">
                    <a class="admin-nav-link <?php echo in_array($adminPage, ['product.php', 'productlist.php', 'edit.php'], true) ? 'is-active' : ''; ?>" href="productlist.php"><svg aria-hidden="true"><use href="../User/auth-icons.svg#book"></use></svg>Books</a>
                </li>
                <li class="nav-item">
                    <a class="admin-nav-link <?php echo $adminPage === 'order_mgmt.php' ? 'is-active' : ''; ?>" href="order_mgmt.php"><svg aria-hidden="true"><use href="../User/auth-icons.svg#receipt"></use></svg>Orders</a>
                </li>
                <li class="nav-item">
                    <a class="admin-nav-link <?php echo in_array($adminPage, ['user.php', 'userlist.php'], true) ? 'is-active' : ''; ?>" href="userlist.php"><svg aria-hidden="true"><use href="../User/auth-icons.svg#users"></use></svg>Customers</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="admin-nav-link dropdown-toggle <?php echo $catalogActive ? 'is-active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><svg aria-hidden="true"><use href="../User/auth-icons.svg#archive"></use></svg>Catalog</a>
                    <ul class="dropdown-menu admin-catalog-menu">
                        <li><a class="dropdown-item" href="category.php">Categories</a></li>
                        <li><a class="dropdown-item" href="author.php">Authors</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="admin-nav-link <?php echo $adminPage === 'feedback.php' ? 'is-active' : ''; ?>" href="feedback.php"><svg aria-hidden="true"><use href="../User/auth-icons.svg#message-square"></use></svg>Feedback</a>
                </li>
            </ul>
            <div class="admin-account">
                <span class="admin-account__identity"><small>Signed in as</small><strong><?php echo e($_SESSION['admin'] ?? 'Admin'); ?></strong></span>
                <form action="logout.php" method="post"><?php echo csrf_field(); ?><button type="submit" class="admin-logout"><svg aria-hidden="true"><use href="../User/auth-icons.svg#logout"></use></svg><span>Sign out</span></button></form>
            </div>
        </div>
    </div>
</nav>
</header>
