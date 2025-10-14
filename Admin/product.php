<?php
session_start();
    include 'connect.php';
    include 'function.php';
        if(isset($_POST['btnaddproduct']))
            {
                addproduct();
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
        </div>
            <div class="row">
                <div class="col-md-6 offset-3">
                    <div class="card text-info">
                        <div class="card-header">Add Book</div>
            </div>
                <div class="card-body">
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="" class="form-label">Book Name</label>
                                <input type="text" name="txtproductname" class="form-control">
                        </div>
                        <form action="" method="post" enctype="multipart/form-data">
                
                <div class="mb-3">
                    <label for="" class="form-label">Author Name</label>
                        <select name="author" id="" class="form-select">
                        <option value="">-----Select One-----</option>
                            <?php
                            $query="Select * from author";
                            $go_query=mysqli_query($connection,$query);
                            while($row=mysqli_fetch_array($go_query))
                            {
                            $authorid=$row['authorid'];
                            $authorname=$row['authorname'];
                                {
                                    echo "<option value={$authorid}>{$authorname}</option>";
                                }
                            }
                            ?>
                        </select>
                </div>

                <div class="mb-3">
                    <label for="" class="form-label">Category Name</label>
                        <select name="category" id="" class="form-select">
                        <option value="">-----Select One-----</option>
                            <?php
                            $query="Select * from categorys";
                            $go_query=mysqli_query($connection,$query);
                            while($row=mysqli_fetch_array($go_query))
                            {
                            $catid=$row['catid'];
                            $catname=$row['catname'];
                                {
                                    echo "<option value={$catid}>{$catname}</option>";
                                }
                            }
                            ?>
                        </select>
                </div>
            
            <div class="mb-3">
                <label for="" class="form-label">Description</label>
                    <input type="text" name="desc" class="form-control">
            </div>
            <div class="mb-3">
                <label for="" class="form-label">Price</label>
                    <input type="text" name="price" class="form-control">
            </div>
            <div class="mb-3">
                <label for="" class="form-label">Quantity</label>
                    <input type="text" name="qty" class="form-control">
            </div>
            <div class="mb-3">
                <label for="" class="form-label">Photo</label>
                    <input type="file" name="photo" class="form-control">

            <div class="mb-3 d-grid">
                    <input type="submit" value="Add Book" name="btnaddproduct" class="btn btn-outline-info">
            </div>
            </form>
</div>
</div>
</div>
</body>
</html>