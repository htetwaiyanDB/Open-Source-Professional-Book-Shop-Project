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
    <style>
        .payment-options { display: grid; gap: .85rem; grid-template-columns: repeat(auto-fit, minmax(215px, 1fr)); }
        .payment-option {
            position: relative;
            display: flex;
            gap: .85rem;
            align-items: flex-start;
            padding: 1rem 1.1rem;
            background: #fff;
            border: 1.5px solid #e3e6ea;
            border-radius: 14px;
            cursor: pointer;
            transition: border-color .18s ease, box-shadow .18s ease;
        }
        .payment-option:hover { border-color: #b9dfe0; box-shadow: 0 6px 18px rgba(8, 157, 161, .08); }
        .payment-option-input { position: absolute; width: 1px; height: 1px; opacity: 0; margin: 0; }
        .payment-option-icon {
            flex: 0 0 42px;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f7f7;
            color: #089da1;
            font-size: 1.1rem;
        }
        .payment-option-text { flex: 1 1 auto; }
        .payment-option-title { display: flex; align-items: center; gap: .4rem; font-weight: 600; color: #1f2d3d; }
        .payment-option-desc { display: block; margin-top: .2rem; font-size: .84rem; line-height: 1.4; color: #6c757d; }
        .payment-option-tick {
            margin-left: auto;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 1.5px solid #cfd6de;
            color: transparent;
            font-size: .6rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .payment-option.is-selected { border-color: #089da1; box-shadow: 0 10px 24px rgba(8, 157, 161, .14); }
        .payment-option.is-selected .payment-option-icon { background: #089da1; color: #fff; }
        .payment-option.is-selected .payment-option-tick { background: #089da1; border-color: #089da1; color: #fff; }
        .payment-option:focus-within { outline: 3px solid rgba(8, 157, 161, .3); outline-offset: 2px; }
        .payment-note { font-size: .82rem; color: #6c757d; margin: .8rem 0 0; }
        .btn-place-order { background-color: #089da1; border-color: #089da1; font-weight: 600; padding: .7rem 1rem; }
        .btn-place-order:hover, .btn-place-order:focus { background-color: #077b7e; border-color: #077b7e; }
        @media (prefers-reduced-motion: reduce) { .payment-option { transition: none; } }
    </style>
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

                <fieldset class="mb-4">
                    <legend class="form-label fs-6 fw-semibold">Payment Method</legend>
                    <div class="payment-options">
                        <label class="payment-option is-selected" for="payment_cod">
                            <input class="payment-option-input" type="radio" name="payment_method" id="payment_cod" value="cod" checked>
                            <span class="payment-option-icon"><i class="fas fa-money-bill-wave" aria-hidden="true"></i></span>
                            <span class="payment-option-text">
                                <span class="payment-option-title">Cash on Delivery<span class="payment-option-tick"><i class="fas fa-check" aria-hidden="true"></i></span></span>
                                <span class="payment-option-desc">Pay the courier in cash when your books arrive. No advance payment needed.</span>
                            </span>
                        </label>
                        <label class="payment-option" for="payment_mmqr">
                            <input class="payment-option-input" type="radio" name="payment_method" id="payment_mmqr" value="mmqr">
                            <span class="payment-option-icon"><i class="fas fa-qrcode" aria-hidden="true"></i></span>
                            <span class="payment-option-text">
                                <span class="payment-option-title">MMQR <span class="badge rounded-pill bg-warning text-dark">Demo</span><span class="payment-option-tick"><i class="fas fa-check" aria-hidden="true"></i></span></span>
                                <span class="payment-option-desc">Scan with KBZPay, WavePay, AYA Pay or CB Pay. A QR popup opens as soon as you place the order.</span>
                            </span>
                        </label>
                    </div>
                    <p class="payment-note">
                        <i class="fas fa-shield-halved me-1" aria-hidden="true"></i>
                        MMQR is a demo: the popup QR is generated in your browser, no money moves and we never ask
                        for card numbers or banking credentials.
                    </p>
                </fieldset>

                <button type="submit" name="submit" value="1" class="btn btn-place-order w-100 text-white">
                    <i class="fas fa-lock me-1" aria-hidden="true"></i> Place Order
                </button>

            </form>

        </div>

    </div>

</div>
 

<script>
    (function () {
        var options = document.querySelectorAll('.payment-option');
        options.forEach(function (option) {
            var input = option.querySelector('.payment-option-input');
            if (!input) { return; }
            input.addEventListener('change', function () {
                options.forEach(function (item) { item.classList.remove('is-selected'); });
                if (input.checked) { option.classList.add('is-selected'); }
            });
        });
    }());
</script>

</body>

</html>
