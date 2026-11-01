<!DOCTYPE html>
<html lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
<?php
session_start();
include 'connect.php';
include 'function.php';
if(isset($_POST['btnaddauthor']))
{
    addauthor();
}
if(isset($_GET['action'])&& $_GET['action']=='delete')
{
    delauthor();
}
if(isset($_POST['btnupdateauthor']))
{
    updateauthor();
}
?>
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
                        echo $_SESSION['admin'];
                    }
                    else
                    {
                        $_SESSION['admin']='';
                    }
                    ?>
                </span>
            </h2>
        </div>

    </div><br>
<div class="container">
<div class="row">
<div class="col-md-6">
    <div class="card">
        <div class="card-header text-center bg-light">
        Add Author
        </div>
        <div class="card-body">
            <form action="" method="post">
                <div class="mb-3">
                    <label for="" class="form-label">
                    Author name
                    </label>
                    <input type="text" name="authorname" class="form-control">
                </div>
                <div class="d-grid">
                    <input type="submit" name="btnaddauthor" value="Add Author" class="btn btn-outline-info">
                </div>
            </form>       
        </div>
    </div>
    <hr>
    <?php
        if(isset($_GET['action'])&& $_GET['action']=='edit')
            {
                $authorid=$_GET['aid'];
                $query="Select  * from author where authorid='$authorid'";
                $go_query=mysqli_query($connection,$query);
                while($out=mysqli_fetch_array($go_query))
                {
                    $authorname=$out['authorname'];//fieldname
    ?>
    <div class="card">
        <div class="card-header text-center bg-light">
        Update Author
        </div>
        <div class="card-body">
            <form action="" method="post">
                <div class="mb-3">
                <label for="" class="form-label">
                 Author Name
                </label>
                <input type="text" name="updateauthor" class="form-control" value="<?php echo $authorname  ?>">
                </div>
                <div class="d-grid">
                <input type="submit" name="btnupdateauthor" value="Update Author" class="btn btn-outline-info">
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
                    $go_query="Select  * from author";
                    $query=mysqli_query($connection,$go_query);
                    while($row=mysqli_fetch_array($query))
                    {
                        $authorid=$row['authorid'];//fieldname
                        $authorname=$row['authorname'];
                        
                        
                        echo "<tr>";
                        echo "<td>{$authorid}</td>";
                        echo "<td>{$authorname}</td>";
                       
                        echo "<td>
                                <a href='author.php?action=delete&aid={$authorid}' class='btn btn-danger' onclick='return confirm('Are you sure?')'>Delete</a> ||
                                <a href='author.php?action=edit&aid={$authorid}' class='btn btn-outline-success' >Edit</a>
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