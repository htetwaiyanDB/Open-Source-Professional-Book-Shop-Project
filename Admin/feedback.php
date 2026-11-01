<?php
session_start();
include 'connect.php';
include 'function.php';
if (isset($_GET['action']) && $_GET['action'] == 'delete' && function_exists('delfeedback')) {
    delfeedback();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback</title>
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
    </div>

    <div class="table-responsive">
        <table class="table table-hover bg-white">
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Message</th>
            </tr>
            <?php
            $go_query = "SELECT * FROM feedback ORDER BY feedbackid DESC";
            $query = mysqli_query($connection, $go_query);
            while ($row = mysqli_fetch_array($query)) {
                $feedid = $row['feedbackid'];
                $name = $row['name'];
                $email = $row['email'];
                $phone = $row['phone'];
                $message = $row['message'];
                echo "<tr>";
                echo "<td>{$feedid}</td>";
                echo "<td>{$name}</td>";
                echo "<td>{$email}</td>";
                echo "<td>{$phone}</td>";
                echo "<td>{$message}</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</div>
</body>
</html>
