<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
$userPage = basename($_SERVER['PHP_SELF']);
?>


<html lang="en">
<head>
<link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Document</title>
<link rel="stylesheet" href="userheader.css"> <!-- Link to external CSS file -->
<link rel="stylesheet" href="../assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light sticky-top bg-light">
        <div class="container-fluid">
            <a class="navbar-brand text-dark" href="home.php">
                <img src="../Photos/logo.png" style="width: 100px; height:50px;" alt="Book Palace">
                
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link text-dark <?php echo in_array($userPage, ['home.php', 'userhome.php'], true) ? 'active' : ''; ?>" <?php echo in_array($userPage, ['home.php', 'userhome.php'], true) ? 'aria-current="page"' : ''; ?> href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark <?php echo in_array($userPage, ['userindex.php', 'product_detail.php']) ? 'active' : ''; ?>" href="userindex.php">All Books</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark <?php echo $userPage === 'newrelease.php' ? 'active' : ''; ?>" href="newrelease.php">Newrelease</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark <?php echo $userPage === 'bestseller.php' ? 'active' : ''; ?>" href="bestseller.php">Bestseller</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark <?php echo $userPage === 'contact.php' ? 'active' : ''; ?>" href="contact.php">Contact Us</a>
                    </li>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link text-dark <?php echo in_array($userPage, ['login.php', 'register.php']) ? 'active' : ''; ?>" href="login.php">Login</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                    <form class="d-flex" action="userindex.php" method="get">
                        <div class="dropdown" id="categoryDropdown">
                            <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php
                                if (isset($_GET['category_id']) && $_GET['category_id'] !== '') {
                                    $result = db_query($connection, 'SELECT catname FROM categorys WHERE catid = ?', [(int) $_GET['category_id']]);
                                    $row = db_fetch_assoc($result);
                                    echo e($row['catname'] ?? 'All Categories');
                                } else {
                                    echo "All Categories";
                                }
                                ?>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item" href="userindex.php">All Categories</a></li>
                                <?php
                                $query = "SELECT * FROM categorys";
                                $result = db_query($connection, $query);
                                while ($row = db_fetch_assoc($result)) {
                                    $selected = (isset($_GET['category_id']) && $row['catid'] == $_GET['category_id']) ? 'active' : '';
                                    echo "<li><a class='dropdown-item $selected' href='userindex.php?category_id=" . e($row['catid']) . "'>" . e($row['catname']) . "</a></li>";
                                }
                                ?>
                            </ul>
                        </div>
                    </form>

                    </li>
                </ul><br>
               
                <ul class="navbar-nav  mb-lg-0">
                    <li class="nav-item">
                        <a href="cart.php" class="nav-link text-dark header-icon-link" aria-label="Shopping cart" title="Shopping cart"><i class="fas fa-cart-shopping" aria-hidden="true"></i></a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a href="userProfile.php" class="nav-link text-dark header-icon-link header-profile-link <?php echo $userPage === 'userProfile.php' ? 'active' : ''; ?>" aria-label="Account and order tracking" title="Account and order tracking">
                            <i class="far fa-user" aria-hidden="true"></i>
                            <span class="visually-hidden">My account</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>

                <form class="d-flex" action="userindex.php" method="get">
                    <input class="form-control me-2" type="search" name="search_query" placeholder="Search" aria-label="Search">
                    <button class="btn btn-outline-info" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>
    <script defer src="../assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="userheader.js"></script> <!-- Link to external JS file -->
</body>
</html>
