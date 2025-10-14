<?php
session_start();
include 'connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <style>
        .card-title {
  color: #262626;
  font-size: 1.5em;
  line-height: normal;
  font-weight: 700;
  margin-bottom: 0.5em;
}

.small-desc {
  font-size: 1em;
  font-weight: 400;
  line-height: 1.5em;
  color: #452c2c;
}

.small-desc {
  font-size: 1em;
}

.go-corner {
  display: flex;
  align-items: center;
  justify-content: center;
  position: absolute;
  width: 2em;
  height: 2em;
  overflow: hidden;
  top: 0;
  right: 0;
  background: linear-gradient(135deg, #6293c8, #384c6c);
  border-radius: 0 4px 0 32px;
}

.go-arrow {
  margin-top: -4px;
  margin-right: -4px;
  color: white;
  font-family: courier, sans;
}

.card {
  display: block;
  position: relative;
  max-width: 300px;
  max-height: 320px;
  background-color: #f2f8f9;
  border-radius: 10px;
  padding: 2em 1.2em;
  margin: 12px;
  text-decoration: none;
  z-index: 0;
  overflow: hidden;
  background: linear-gradient(to bottom, #c3e6ec, #a7d1d9);
  font-family: Arial, Helvetica, sans-serif;
}

.card:before {
  content: '';
  position: absolute;
  z-index: -1;
  top: -16px;
  right: -16px;
  background: linear-gradient(135deg, #364a60, #384c6c);
  height: 32px;
  width: 32px;
  border-radius: 32px;
  transform: scale(1);
  transform-origin: 50% 50%;
  transition: transform 0.35s ease-out;
}

.card:hover:before {
  transform: scale(28);
}

.card:hover .small-desc {
  transition: all 0.5s ease-out;
  color: rgba(255, 255, 255, 0.8);
}

.card:hover .card-title {
  transition: all 0.5s ease-out;
  color: #ffffff;
}

    </style>
</head>
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
      <p class="card-title">Category</p>
      <p class="small-desc">
      <a href="category.php" class="btn btn-dark">View Details 
                        <span class="badge">
                        <?php
                        $query="Select * from categorys";
                        $get_query=mysqli_query($connection,$query);
                        $num=mysqli_num_rows($get_query);
                        echo $num;
                        ?>
                        </span>
                        </a>
      </p>
</div>
</div><br>
<div class="col-md-3">
<div class="card">
      <p class="card-title">Author</p>
      <p class="small-desc">
      <a href="author.php" class="btn btn-dark">View Details 
                        <span class="badge">
                        <?php
                        $query="Select * from author";
                        $get_query=mysqli_query($connection,$query);
                        $num=mysqli_num_rows($get_query);
                        echo $num;
                        ?>
                        </span>
      </a>
      </p>
</div>
</div><br>
<div class="col-md-3">
<div class="card">
      <p class="card-title">User</p>
      <p class="small-desc">
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
      </p>
</div>
</div><br>
<div class="col-md-3">
<div class="card">
      <p class="card-title">Book</p>
      <p class="small-desc">
      <a href="productlist.php" class="btn btn-dark">View Details 
                        <span class="badge">
                        <?php
                        $query="Select * from products";
                        $get_query=mysqli_query($connection,$query);
                        $num=mysqli_num_rows($get_query);
                        echo $num;
                        ?>
                        </span>
      </a>
      </p>
</div>
</div><br>
<div class="col-md-3">
<div class="card">
      <p class="card-title">Order</p>
      <p class="small-desc">
      <a href="order_mgmt.php" class="btn btn-dark">View Details 
                        <span class="badge">
                        <?php
                          $query="Select * from orders";
                          $get_query=mysqli_query($connection,$query);
                          $num=mysqli_num_rows($get_query);
                          echo $num;
                          ?>
                        </span>
       </a>
      </p>
</div>
</div><br>
<div class="col-md-3">
<div class="card">
      <p class="card-title">Feedback</p>
      <p class="small-desc">
      <a href="feedback.php" class="btn btn-dark">View Details 
                        <span class="badge">
                        <?php
                          $query="Select * from feedback";
                          $get_query=mysqli_query($connection,$query);
                          $num=mysqli_num_rows($get_query);
                          echo $num;
                          ?>
                        </span>
       </a>
      </p>
</div>
</div><br>
<div class="row mt-3">
        <div class="col-sm-6">
        <a href="product.php" class="btn btn-info">Add Book</a>
        <a href="user.php" class="btn btn-info">Add User</a>
        </div>
</div>
</div>
</body>
</html>  
