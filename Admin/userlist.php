<?php
session_start();
include 'connect.php';
include 'function.php';
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    deluser();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List</title>
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
            <h2>Welcome Admin,
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
        </div>
        <div class="col-md-4 text-md-end">
            <a href="user.php" class="btn btn-outline-info">Add User</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover bg-white">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Role</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
            <?php
            $go_query = "SELECT * FROM user ORDER BY userid DESC";
            $query = mysqli_query($connection, $go_query);
            while ($row = mysqli_fetch_array($query)) {
                $userid = $row['userid'];
                $username = $row['username'];
                $user_role = $row['role'];
                $user_email = $row['email'];
                echo "<tr>";
                echo "<td>{$userid}</td>";
                echo "<td>{$username}</td>";
                echo "<td>{$user_role}</td>";
                echo "<td>{$user_email}</td>";
                echo "<td><a href='userlist.php?action=delete&uid={$userid}' class='btn btn-danger btn-sm' onclick=\"return confirm('Are you sure?')\">Delete</a></td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</div>
</body>
</html>
