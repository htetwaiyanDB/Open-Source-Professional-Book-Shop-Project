<?php
session_start();
include 'connect.php';
include 'function.php';
if(isset($_GET['action'])&& $_GET['action']=='delete')
{
    delfeedback();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
<div class="col-md-12">
<table class="table table-hover">
<tr>
<th>ID</th>
<th>Username</th>
<th>Email</th>
<th>Phone Number</th>
<th>Message</th>
</tr>
<?php
                    $go_query="Select  * from feedback order by feedbackid desc";
                    $query=mysqli_query($connection,$go_query);
                    while($row=mysqli_fetch_array($query))
                    {
                        $feedid=$row['feedbackid'];//fieldname
                        $name=$row['name'];
                        $email=$row['email'];
                        $phone=$row['phone'];
                        $message=$row['message'];
                        echo "<tr>";
                        echo "<td>{$feedid}</td>";
                        echo "<td>{$name}</td>";
                        echo "<td>{$email}</td>";
                        echo "<td>{$phone}</td>";
                        echo "<td>{$message}</td>";
                        echo "<td>
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