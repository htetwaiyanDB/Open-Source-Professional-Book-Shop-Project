<?php
require_once __DIR__ . '/connect.php';
require_admin();
require_once __DIR__ . '/function.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    verify_csrf();
    $fid = filter_var($_POST['fid'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($fid) {
        db_query($connection, 'DELETE FROM feedback WHERE feedbackid = ?', [$fid]);
    }
    redirect('feedback.php');
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
            $feedback = db_query($connection, 'SELECT feedbackid, name, email, phone, message FROM feedback ORDER BY feedbackid DESC');
            while ($row = db_fetch_assoc($feedback)) {
                $feedid = (int) $row['feedbackid'];
                $name = e($row['name']);
                $email = e($row['email']);
                $phone = e($row['phone']);
                $message = e($row['message']);
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
