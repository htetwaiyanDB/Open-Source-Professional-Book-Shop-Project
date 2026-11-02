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

                <fieldset class="mb-3">
                    <legend class="form-label fs-6">Payment Method</legend>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment_method" id="payment_cod" value="cod" checked>
                        <label class="form-check-label" for="payment_cod">
                            <strong>Cash on Delivery</strong> &mdash; pay the courier when your books arrive.
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="payment_mmqr" value="mmqr">
                        <label class="form-check-label" for="payment_mmqr">
                            <strong>MMQR</strong> &mdash; scan with KBZPay, WavePay, AYA Pay, CB Pay or any MMQR app.
                            <span class="badge bg-warning text-dark">Demo</span>
                        </label>
                    </div>
                </fieldset>

                <p class="text-muted">The MMQR option is a demo: a sample QR is shown after you submit and no money moves. Never enter card numbers or banking credentials.</p>

                <input type="submit" name="submit" value="Submit" class="btn btn-primary" />

            </form>

        </div>

    </div>

</div>
 

</body>

</html>
