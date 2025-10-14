<?php
session_start();
include 'connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../admin/d.css" rel="stylesheet">
    <title>Document</title>

</head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<body> 
    <?php
    include 'header.php';
    ?><br>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h3>Welcome Admin,
                    <span class="text-danger">
                    <?php
                    if(isset($_SESSION['admin']))
                    {
                        echo $_SESSION['admin'];
                    }
                    else
                    {
                        $_SESSION['admin']='';
                    }
                    ?>

                    </span>
                </h3>
            </div>
        </div><br>
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-light text-center">Category</div>
                    <div class="card-body">
                        <a href="category.php" class="btn btn-info">View Details 
                        <span class="badge">
                        <?php
                        $query="Select * from categorys";
                        $get_query=mysqli_query($connection,$query);
                        $num=mysqli_num_rows($get_query);
                        echo $num;
                        ?>
                        </span>
                        </a>
                    </div>
                </div>
            </div><br>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-light text-center">Author</div>
                    <div class="card-body">
                        <a href="author.php" class="btn btn-primary">View Details 
                        <span class="badge">
                        <?php
                        $query="Select * from author";
                        $get_query=mysqli_query($connection,$query);
                        $num=mysqli_num_rows($get_query);
                        echo $num;
                        ?>
                        </span>
                        </a>
                    </div>
                </div>
            </div>
            <br>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-light text-center">User</div>
                    <div class="card-body">
                        <a href="userlist.php" class="btn btn-dark">View Details 
                        <span class="badge">
                        <?php
                          $query="Select * from user";
                          $get_query=mysqli_query($connection,$query);
                          $num=mysqli_num_rows($get_query);
                          echo $num;
                          ?>
                        </span>
                        </a>
                    </div>
                </div>
            </div>
            <br>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-light text-center">Product</div>
                    <div class="card-body">
                        <a href="productlist.php" class="btn btn-danger">View Details 
                        <span class="badge">
                        <?php
                          $query="Select * from products";
                          $get_query=mysqli_query($connection,$query);
                          $num=mysqli_num_rows($get_query);
                          echo $num;
                          ?>
                        </span>
                        </a>
                    </div>
                </div>
            </div>
            <br>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-light text-center">Order</div>
                    <div class="card-body">
                        <a href="order_mgmt.php" class="btn btn-info">View Details 
                        <span class="badge">
                        <?php
                          $query="Select * from orders";
                          $get_query=mysqli_query($connection,$query);
                          $num=mysqli_num_rows($get_query);
                          echo $num;
                          ?>
                        </span>
                        </a>
                    </div>
                </div>
            </div><br>
        </div>
        <div class="row mt-3">
        <div class="col-sm-12">
        <a href="product.php" class="btn btn-info">Add Book</a>
        <a href="user.php" class="btn btn-info">Add User</a>
        </div>
        </div>
    </div>
    </div>
</body>
</html>