<?php
session_start();
include 'connect.php';
include 'function.php';

if(isset($_POST['btnlogin']))
{
    admin_login();//function call
}

?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Document</title>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <link rel='stylesheet' href='.css'>
</head>

<body>
        <section>
            <div class="form-box">
                <div class="form-value">
                    <form action="" method="post">
                        <h2>Login</h2>
                        <div class="inputbox"> <ion-icon name="mail-outline"></ion-icon> <input type="text" name="username" required>
                            <label>Email / Username</label>
                        </div>
                        <div class="inputbox"> <ion-icon name="lock-closed-outline"></ion-icon> <input type="password" name="password"
                                required> <label>Password</label> </div>
                        <button type="submit" name="btnlogin">Login</button>
    
                    </form>
                </div>
            </div>
        </section> 
</body>

</html>