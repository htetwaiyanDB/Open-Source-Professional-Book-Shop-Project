<?php
require_once __DIR__ . '/connect.php';
require_admin();


require_once __DIR__ . '/function.php';
if(isset($_POST['btnaddcat']))
{
    verify_csrf();
    addcat();
}
if(isset($_POST['action']) && $_POST['action'] === 'delete')
{
    verify_csrf();
    delcat();
}
if(isset($_POST['btnupdatecategory']))
{
    verify_csrf();
    updatecat();
}

?>
<!DOCTYPE html>
<html lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .login-box .user-box input {
  width: 100%;
  padding: 10px 0;
  font-size: 16px;
  color: #fff;
  margin-bottom: 30px;
  border: none;
  border-bottom: 1px solid #fff;
  outline: none;
  background: transparent;
}

.login-box .user-box label {
  position: absolute;
  top: 0;
  left: 0;
  padding: 10px 0;
  font-size: 16px;
  color: #fff;
  pointer-events: none;
  transition: .5s;
}

.login-box .user-box input:focus ~ label,
.login-box .user-box input:valid ~ label {
  top: -20px;
  left: 0;
  color: #bdb8b8;
  font-size: 12px;
}

.login-box form a {
  position: relative;
  display: inline-block;
  padding: 10px 20px;
  color: #ffffff;
  font-size: 16px;
  text-decoration: none;
  text-transform: uppercase;
  overflow: hidden;
  transition: .5s;
  margin-top: 40px;
  letter-spacing: 4px
}

.login-box a:hover {
  background: #03f40f;
  color: #fff;
  border-radius: 5px;
  box-shadow: 0 0 5px #03f40f,
              0 0 25px #03f40f,
              0 0 50px #03f40f,
              0 0 100px #03f40f;
}

.login-box a span {
  position: absolute;
  display: block;
}

@keyframes btn-anim1 {
  0% {
    left: -100%;
  }

  50%,100% {
    left: 100%;
  }
}

.login-box a span:nth-child(1) {
  bottom: 2px;
  left: -100%;
  width: 100%;
  height: 2px;
  background: linear-gradient(90deg, transparent, #03f40f);
  animation: btn-anim1 2s linear infinite;
}
    </style>

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Document</title>
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<link rel="stylesheet" href="../assets/vendor/bootstrap/bootstrap.min.css">
<script defer src="../assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<body>
    <?php
    include 'header.php';
    ?><br>
    <div class="row">
        <div class="col-md-12">
            <h2>Welcome Admin,
                <span class="text-danger">
                <?php
                    if(isset($_SESSION['admin']))
                    {
                        echo e($_SESSION['admin']);
                    }
                    else
                    {
                        $_SESSION['admin']='';
                    }
                    ?>
                </span>
            </h2>
        </div>

    </div><BR>
<div class="container">
<div class="row">
<div class="col-md-6">
    <div class="card">
        <div class="card-header text-center bg-light">
        Add Category
        </div>
        <div class="card-body">
            <form action="" method="post">
<?php echo csrf_field(); ?>
                <div class="mb-3">
                    <label for="" class="form-label">
                    Category Name
                    </label>
                    <input type="text" name="catname" class="form-control">
                </div>
                <div class="d-grid">
                    <input type="submit" name="btnaddcat" value="Add Category" class="btn btn-outline-info">
                </div>
            </form>       
        </div>
    </div>
    <hr>
    <?php
        if(isset($_GET['action'])&& $_GET['action']=='edit')
            {
                $catid=$_GET['cid'];
                $query="Select  * from categorys where catid=?";
                $go_query=db_query($connection,$query, [(int) $catid]);
                while($out=db_fetch_array($go_query))
                {
                    $catname=$out['catname'];//fieldname
    ?>
    <div class="card">
        <div class="card-header text-center bg-info">
        Update Category
        </div>
        <div class="card-body">
            <form action="" method="post">
<?php echo csrf_field(); ?>
                <div class="mb-3">
                <label for="" class="form-label"class="user-box">
                Category Name
                </label>
                <input type="text" name="updatecategory" class="form-control" value="<?php echo e($catname); ?>">
                </div>
                <div class="d-grid">
                <input type="hidden" name="cid" value="<?php echo (int) $catid; ?>"><input type="submit" name="btnupdatecategory" value="Update Category" class="btn btn-outline-info">
                </div>
            </form>          
        </div>
    </div>
    <?php
                }
            }
    ?>
</div>
<div class="col-md-6">
    <table class="table table-hover">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Action</th>

        </tr>
        <?php
                    $go_query="Select  * from categorys";
                    $query=db_query($connection,$go_query);
                    while($row=db_fetch_array($query))
                    {
                        $catid=$row['catid'];//fieldname
                        $catname=$row['catname'];
                        
                        
                        echo "<tr>";
                        echo "<td>" . e($catid) . "</td>";
                        echo "<td>" . e($catname) . "</td>";
                       
                        echo "<td>
                                <form action='category.php' method='post' style='display:inline'>" . csrf_field() . "<input type='hidden' name='action' value='delete'><input type='hidden' name='cid' value='{$catid}'><button type='submit' class='btn btn-danger'>Delete</button></form> ||
                                <a href='category.php?action=edit&cid={$catid}' class='btn btn-outline-success' >Edit</a>
                                </td>";
                        echo "</tr>";
                    }
                    ?>
    </table>
</div>
</div>
</div>
</body>
</html>