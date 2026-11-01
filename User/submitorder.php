<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
$user = require_login();
if (empty($_SESSION['cart'])) { redirect('cart.php'); }
$user_name = $user['username'];
$email = $user['email'];
$phone = $user['phone'];
$address = $user['address'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Untitled Document</title>
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<body>

<?php include 'userheader.php' ?>

<div class="container mt-5">
    <div class="row">
    <div class="col-sm-6">
      
        <h3>Welcome ,
         <span class="text-danger">
            <?php
            if (!empty($_SESSION['user'])) {
              echo e($_SESSION['user']);
            } else {
              $_SESSION['user'] = '';
            }
            ?>
          </span>
        </h3>
     
    </div>
    </div>
     <div class="row">
        <div class="col-md-6 offset-md-3">
            <form action="submit.php" method="post">
<?php echo csrf_field(); ?>
                <div class="mb-3">
                    <label for="username" class="form-label">User Name</label>
                    <input type="text" value="<?php if(isset($user_name)){echo e($user_name);}?>" name="username" class="form-control" />
                </div>

                <div class="mb-3">

                    <label for="email" class="form-label">Email</label>

                    <input type="text" value="<?php if(isset($email)){echo e($email);}?>" name="email" class="form-control" />

                </div>

                <div class="mb-3">

                    <label for="phone" class="form-label">Phone</label>

                    <input type="text" value="<?php if(isset($phone)){echo e($phone);}?>" name="phone" class="form-control" />

                </div>

                <div class="mb-3">

                    <label for="address" class="form-label">Address</label>

                    <textarea name="address" class="form-control"><?php if(isset($address)){echo e($address);}?></textarea>

                </div>

                <p class="text-muted">No online payment is collected here. Payment and delivery will be arranged separately. Never enter card numbers or banking credentials.</p>

                <input type="submit" name="submit" value="Submit" class="btn btn-primary" />

            </form>

        </div>

    </div>

</div>
 

</body>

</html>
