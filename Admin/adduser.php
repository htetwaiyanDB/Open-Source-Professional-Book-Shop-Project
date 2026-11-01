<?php
require_once __DIR__ . '/connect.php';
require_admin();


            
            require_once __DIR__ . '/function.php';
    if (isset($_POST['btnadduser'])) { verify_csrf(); adduser(); }
?>
<body>
<?php
                include 'header.php';
        ?>

        <div class="container Top">
                <div class="row">
                    <div class="col-md-12">
                <div class="row">
                    <div class="page-header">
                        <h2>Welcome admin,<span class="text-danger">
                            <?php
                                if(isset($_SESSION['admin'])){
                                    echo e($_SESSION['admin']);
                                }else {
                                    $_SESSION['admin']='';
                                }
                            ?>
                        </span></h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <form method="post">
<?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label for="">User Name</label>
                                    <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="">Email</label><input type="email" name="email" class="form-control" required></div><div class="form-group"><label for="">Password</label>
                                    <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="">Confirm Pasword</label>
                                    <input type="password" name="cpassword" class="form-control" required>
                            </div>
                            <div class="form-gorup">
                                <label for="">User Type"</label>
                                    <select name="role" class="form-control" id="">
                                        <option value="admin">----Admin----</option>
                                        <option value="user">----User----</option>
                                    </select>
                            </div>
                            <button type="submit" name="btnadduser" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
                    </div>
                </div>

        </div>
</body>
</html>