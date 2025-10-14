<?php
session_start();
include 'connect.php';
include 'function.php';
if(isset($_GET['action'])&& $_GET['action']=='delete')
{
    del_product();
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
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header text-center">Books List</div>
                    <table class="table table-hover">
                        <tr>
                            <th>No</th>
                            <th>Photo</th>
                            <th>Book Name</th>
                            <th>Author Name</th>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Quantity</th>                            
                            <th>Action</th>
                        </tr>
                        <?php
                    $go_query = "SELECT products.*, categorys.*, author.authorname from products join categorys on products.categoryid = categorys.catid join author on products.authorid = author.authorid
                    order by products.productid desc ";
                    $query=mysqli_query($connection,$go_query);
                    while($row=mysqli_fetch_array($query))
                    {
                        $productid=$row['productid'];//fieldname_product
                        $productname=$row['productname'];//fieldname
                        $authorname=$row['authorname'];//author table_fieldname
                        $catname=$row['catname'];//category table_fieldname
                        $desc=$row['description'];//products fieldname
                        $price=$row['price'];
                        $qty=$row['Qty'];
                        $photo=$row['photo'];
                        echo "<tr>";
                            echo "<td>{$productid}</td>";
                            echo "<td><img src='../photos/{$photo}' width='100' height='100'></td>";
                            echo "<td>{$productname}</td>";
                            echo "<td>{$authorname}</td>";
                            echo "<td>{$catname}</td>";
                            echo "<td>{$desc}</td>";
                            echo "<td>{$price}</td>";
                            echo "<td>{$qty}</td>";
                            echo "<td>
                                <a href='edit.php?action=edit&pid={$productid}' class='btn btn-info'>Edit</a>
                                <br><br>
                                <a href='productlist.php?action=delete&pid={$productid}' class='btn btn-danger' onclick='return confirm ('Are you sure?')'>Delete</a>
                                
                                </td>";
                        echo "</tr>";
                    }
                    ?>

                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>