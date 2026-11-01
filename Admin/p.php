<?php
require_once __DIR__ . '/connect.php';
require_admin();



    require_once __DIR__ . '/function.php';
        if(isset($_POST['btnaddproduct']))
            {
                verify_csrf();
    addproduct();
            }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    
    <style>
        
.form {
    display: flex;
    flex-direction: column;
    gap: 10px;
    background-color: white;
    padding: 2.5em;
    border-radius: 25px;
    transition: .4s ease-in-out;
    
    align-items: center;
    justify-content: center;
  }
  

  
  .heading {
    color: #0cccee;
    padding-bottom: 2em;
    text-align: center;
    font-weight: bold;
  }
  
  .input {
    border-radius: 5px;
    border: 1px solid whitesmoke;
    background-color: whitesmoke;
    outline: none;
    padding: 0.7em;
    transition: .4s ease-in-out;
    
  }
  
  .input:hover {
    box-shadow: 6px 6px 0px #969696,
               -3px -3px 10px #ffffff;
  }
  
  .input:focus {
    background: #ffffff;
    box-shadow: inset 2px 5px 10px rgba(0,0,0,0.3);
  }
  
  .form .btn {
    margin-top: 2em;
    align-self: center;
    padding: 0.7em;
    padding-left: 1em;
    padding-right: 1em;
    border-radius: 10px;
    border: none;
    background-color: #0cccee;
    color: black;
    transition: .4s ease-in-out;
    box-shadow: rgba(0, 0, 0, 0.4) 1px 1px 1px;
  }
  
  .form .btn:hover {
    box-shadow: 6px 6px 0px #969696,
               -3px -3px 10px #ffffff;
    transform: translateX(-0.5em) translateY(-0.5em);
  }
  
  .form .btn:active {
    transition: .2s;
    transform: translateX(0em) translateY(0em);
    box-shadow: none;
  }
    </style>
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<body>

<?php include 'header.php' ?>
<br>
<div class="container">
<div class="row">
<div class="col-md-12">
<h3>Welcome Admin,
<span>
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
</h3>
</div>
</div>
<section>
<form class="form" method="post" enctype="multipart/form-data">
<?php echo csrf_field(); ?>
    <h3 class="heading">Add Book</h3>
    <input type="text" name="txtproductname" placeholder="Bookname" id="" class="input">

   
    <select name="author" id="" class="form-select">
                        <option value="">-----Author Name-----</option>
                            <?php
                            $query="Select * from author";
                            $go_query=db_query($connection,$query);
                            while($row=db_fetch_array($go_query))
                            {
                            $authorid=$row['authorid'];
                            $authorname=$row['authorname'];
                                {
                                    echo "<option value=" . e($authorid) . ">" . e($authorname) . "</option>";
                                }
                            }
                            ?>
    </select>
    </div>
    <select name="category" id="" class="form-select">
                        <option value="">-----Category Name-----</option>
                            <?php
                            $query="Select * from categorys";
                            $go_query=db_query($connection,$query);
                            while($row=db_fetch_array($go_query))
                            {
                            $catid=$row['catid'];
                            $catname=$row['catname'];
                                {
                                    echo "<option value=" . e($catid) . ">" . e($catname) . "</option>";
                                }
                            }
                            ?>
                        </select>
                        </div>
        <input type="text" name="desc" placeholder="Description" id="" class="input"> 
        <input type="text" name="price" placeholder="Price" id="" class="input">
        <input type="text" name="qty" placeholder="Quantity" id="" class="input">
        <input type="file" name="photo" placeholder="Photo" id="" class="input">

    <button class="btn" name="btnaddproduct">Add Book</button>
</form>
</section>

    </body>
</html>
  
