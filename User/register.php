<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
require_once __DIR__ . '/userfunction.php';
if (isset($_POST['register'])) {
    $user_name = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirmpassword'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $errors = array(
        'username' => '',
        'password' => '',
        'confirm_password' => '',
        'match_password' => '',
        'email' => '',
        'phone' => '',
        'address' => '',
    );

    if (empty($user_name)) {
        $errors['username'] = 'User Name must be entered';
    } else {
        if (strlen($user_name) < 3) {
            $errors['username'] = 'User Name needs to be longer';
        }
    }

    if (empty($password)) {
        $errors['password'] = 'This field could not be empty';
    } else {
        if (strlen($password) < 3) {
            $errors['password'] = 'Password needs to be longer';
        }
    }

    if (empty($confirm_password)) {
        $errors['confirm_password'] = 'This field could not be empty';
    } else {
        if ($password != $confirm_password) {
            $errors['match_password'] = 'Password do not match';
        }
    }

    if (empty($email)) {
        $errors['email'] = 'This field could not be empty';
    }

    if (empty($phone)) {
        $errors['phone'] = 'This field could not be empty';
    }

    if (empty($address)) {
        $errors['address'] = 'This field could not be empty';
    }

    foreach ($errors as $key => $value) {
        if (empty($value)) {
            unset($errors[$key]);
        }
    }

    if (empty($errors)) {
        create_accu();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Form</title>
    <link rel="stylesheet" href="login.css?v=20261101-performance">
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<body class="auth-body">
    <main class="auth-shell auth-shell--register">
        <section class="auth-panel auth-panel--intro">
            <div class="auth-badge">Book Palace</div>
            <h1>Create your account.</h1>
            <p>Register once to order faster, keep your details ready, and enjoy a smoother shopping experience.</p>

            <div class="auth-feature-list">
                <div class="auth-feature-item">
                    <svg class="auth-icon" aria-hidden="true"><use href="auth-icons.svg#user-plus"></use></svg>
                    <span>Create your personal account</span>
                </div>
                <div class="auth-feature-item">
                    <svg class="auth-icon" aria-hidden="true"><use href="auth-icons.svg#truck"></use></svg>
                    <span>Checkout more easily later</span>
                </div>
                <div class="auth-feature-item">
                    <svg class="auth-icon" aria-hidden="true"><use href="auth-icons.svg#mail"></use></svg>
                    <span>Keep your contact details ready</span>
                </div>
            </div>
        </section>

        <section class="auth-panel auth-panel--form">
            <div class="auth-topbar">
                <a href="home.php" class="auth-backlink"><svg class="auth-icon" aria-hidden="true"><use href="auth-icons.svg#arrow-left"></use></svg> Back to Home</a>
            </div>

            <div class="auth-form-header">
                <h2>Create account</h2>
                <p>Fill in your details below to get started.</p>
            </div>

            <form action="" method="post" class="auth-form auth-form--register">
<?php echo csrf_field(); echo render_flash(); ?>
                <div class="auth-grid">
                    <label class="auth-field">
                        <span>Username</span>
                        <div class="auth-input">
                            <svg class="auth-icon" aria-hidden="true"><use href="auth-icons.svg#user"></use></svg>
                            <input type="text" name="username" value="<?php echo isset($user_name) ? htmlspecialchars($user_name) : ''; ?>" placeholder="Enter your username" required>
                        </div>
                        <?php if (isset($errors['username'])): ?><small class="auth-error"><?php echo htmlspecialchars($errors['username']); ?></small><?php endif; ?>
                    </label>

                    <label class="auth-field">
                        <span>Email</span>
                        <div class="auth-input">
                            <svg class="auth-icon" aria-hidden="true"><use href="auth-icons.svg#mail"></use></svg>
                            <input type="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" placeholder="Enter your email" required>
                        </div>
                        <?php if (isset($errors['email'])): ?><small class="auth-error"><?php echo htmlspecialchars($errors['email']); ?></small><?php endif; ?>
                    </label>
                </div>

                <div class="auth-grid">
                    <label class="auth-field">
                        <span>Password</span>
                        <div class="auth-input">
                            <svg class="auth-icon" aria-hidden="true"><use href="auth-icons.svg#lock"></use></svg>
                            <input type="password" name="password" placeholder="Enter password" required>
                        </div>
                        <?php if (isset($errors['password'])): ?><small class="auth-error"><?php echo htmlspecialchars($errors['password']); ?></small><?php endif; ?>
                    </label>

                    <label class="auth-field">
                        <span>Confirm Password</span>
                        <div class="auth-input">
                            <svg class="auth-icon" aria-hidden="true"><use href="auth-icons.svg#shield"></use></svg>
                            <input type="password" name="confirmpassword" placeholder="Confirm password" required>
                        </div>
                        <?php if (isset($errors['confirm_password'])): ?><small class="auth-error"><?php echo htmlspecialchars($errors['confirm_password']); ?></small><?php endif; ?>
                        <?php if (isset($errors['match_password'])): ?><small class="auth-error"><?php echo htmlspecialchars($errors['match_password']); ?></small><?php endif; ?>
                    </label>
                </div>

                <div class="auth-grid">
                    <label class="auth-field">
                        <span>Phone</span>
                        <div class="auth-input">
                            <svg class="auth-icon" aria-hidden="true"><use href="auth-icons.svg#phone"></use></svg>
                            <input type="text" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" placeholder="Enter your phone number" required>
                        </div>
                        <?php if (isset($errors['phone'])): ?><small class="auth-error"><?php echo htmlspecialchars($errors['phone']); ?></small><?php endif; ?>
                    </label>

                    <label class="auth-field">
                        <span>Address</span>
                        <div class="auth-input">
                            <svg class="auth-icon" aria-hidden="true"><use href="auth-icons.svg#map-pin"></use></svg>
                            <input type="text" name="address" value="<?php echo isset($address) ? htmlspecialchars($address) : ''; ?>" placeholder="Your current address" required>
                        </div>
                        <?php if (isset($errors['address'])): ?><small class="auth-error"><?php echo htmlspecialchars($errors['address']); ?></small><?php endif; ?>
                    </label>
                </div>

                <button type="submit" class="auth-submit" name="register">Register</button>

                <div class="auth-helper">
                    <span>Already have an account?</span>
                    <a href="login.php">Login</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
