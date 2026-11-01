<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
$user = require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $address = trim((string) ($_POST['address'] ?? ''));
    if (strlen($username) < 3 || strlen($username) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254 || strlen($phone) > 30 || strlen($address) > 1000) {
        flash_error('Please provide a valid name, email, phone and address.');
    } else {
        $saved = false;
        try {
            db_execute($connection, 'UPDATE users SET username = ?, email = ?, phone = ?, address = ? WHERE userid = ?', [$username, $email, $phone, $address, (int) $_SESSION['user_id']]);
            $saved = true;
        } catch (PDOException $error) {
            if ($error->getCode() !== '23505') {
                throw $error;
            }
            flash_error('That username is already taken. Choose another one.');
            $user = current_user_record() ?? $user;
            $username = $user['username'];
            $email = $user['email'];
            $phone = $user['phone'];
            $address = $user['address'];
        }
        if ($saved) {
            $_SESSION['user'] = $username;
            redirect('userProfile.php');
        }
    }
}
$username = $user['username'];
$email = $user['email'];
$phone = $user['phone'];
$address = $user['address'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style.css"/>
    <title>User Profile</title>
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<body><?php include __DIR__ . '/userheader.php'; echo render_flash(); ?>  
    <div class="container">
        <p class="text-muted">Manage your account details below.</p> 
        <form action="userProfile.php" method="post" class="mt-2">
<?php echo csrf_field(); ?> 
            <div class="input-group mt-2">
                <label for="username" class="w-100">Name</label>
                <input type="text" name="username" value="<?php echo e($username); ?>" id="username" class="form-control">
            </div>
            <div class="input-group mt-2">
                <label for="email" class="w-100">Email</label>
                <input type="text" name="email" value="<?php echo e($email); ?>" id="email" class="form-control">
            </div>
            <div class="input-group mt-2">
                <label for="address" class="w-100">Address</label>
                <input type="text" name="address" value="<?php echo e($address); ?>" id="address" class="form-control">
            </div>
            <div class="input-group mt-2">
                <label for="phone" class="w-100">Phone</label>
                <input type="text" name="phone" value="<?php echo e($phone); ?>" id="phone" class="form-control">
            </div>
            <div class="mt-3 text-center">
                <input type="submit" value="Update" name="submit" class="btn btn-primary">
            </div>
        </form>      
    </div>
</body>
</html>
