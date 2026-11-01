<?php
require_once __DIR__ . '/connect.php';
require_admin();
require_once __DIR__ . '/function.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'delete') {
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
            $users = db_query($connection, 'SELECT userid, username, role, email FROM users ORDER BY userid DESC');
            while ($row = db_fetch_assoc($users)) {
                $userid = (int) $row['userid'];
                $username = e($row['username']);
                $user_role = e($row['role']);
                $user_email = e($row['email']);
                echo "<tr>";
                echo "<td>{$userid}</td>";
                echo "<td>{$username}</td>";
                echo "<td>{$user_role}</td>";
                echo "<td>{$user_email}</td>";
                echo "<td><form action='userlist.php' method='post' class='d-inline' onsubmit=\"return confirm('Are you sure?')\">" . csrf_field() . "<input type='hidden' name='action' value='delete'><input type='hidden' name='uid' value='{$userid}'><button type='submit' class='btn btn-danger btn-sm'>Delete</button></form></td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</div>
</body>
</html>
