<?php
if(isset($_POST['btnlogin']))
{
    session_start();
    require_once 'connect.php';
    require_once 'function.php';
    admin_login();//function call
}

?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Document</title>
    <link rel='stylesheet' href='.css'>
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>

<body>
        <section>
            <div class="form-box">
                <div class="form-value">
                    <form action="" method="post">
                        <h2>Login</h2>
                        <div class="inputbox"> <svg class="login-icon" aria-hidden="true"><use href="../User/auth-icons.svg#mail"></use></svg> <input type="text" name="username" required>
                            <label>Email / Username</label>
                        </div>
                        <div class="inputbox"> <svg class="login-icon" aria-hidden="true"><use href="../User/auth-icons.svg#lock"></use></svg> <input type="password" name="password"
                                required> <label>Password</label> </div>
                        <button type="submit" name="btnlogin">Login</button>
    
                    </form>
                </div>
            </div>
        </section> 
</body>

</html>
