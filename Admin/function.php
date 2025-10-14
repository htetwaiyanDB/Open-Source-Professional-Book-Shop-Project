<?php
function adduser()
{
    global $connection;
    $uname=$_POST['username'];
    $email=$_POST['email'];
    $password=$_POST['password'];
    $cpass=$_POST['cpassword'];
    $usertype=$_POST['role'];
      if($password!=$cpass)
      {
        echo"<script>window.alert('Password and comfrim password are must be same')</script>";
      }
      else if($password!="" && $uname!="")
       {
           $query="select * from user where username='$uname' and password=md5('$password')";
           $ch_query=mysqli_query($connection,$query);
           $count=mysqli_num_rows($ch_query);
             if($count>0)
             {
                echo"<script>window.alert('This is already exist')</script>";
             }
             else
             {
                $hashvalue=md5($password);            
                $query="insert into user(username,email,password,role) values('$uname','$email','$hashvalue','$usertype')";
                $go_query=mysqli_query($connection,$query);
                if(!$go_query)
                    {
                        die("QUERY FAILED".mysqli_error());
                    }
                 header("location:userlist.php");
             }
       }
    }
    function deluser()
    {
      global $connection;
      $userid=$_GET['uid'];
      $query="Delete from user where userid='$userid'";
      $go_query=mysqli_query($connection,$query);
      header("location:userlist.php");
    }
    function addcat()
    {
      global $connection;
      $catname=$_POST['catname'];

      if(empty($catname))
      {
        echo '<script>window.alert("Please Enter Category Name")</script>';
      }
      else
      {
        $query="select * from categorys where catname='$catname'";
           $ch_query=mysqli_query($connection,$query);
           $count=mysqli_num_rows($ch_query);
             if($count>0)
             {
                echo"<script>window.alert('This is already exist')</script>";
             }
             else
             {
                            
                $query="insert into categorys(catname) values('$catname')";
                $go_query=mysqli_query($connection,$query);
                if(!$go_query)
                    {
                        die("QUERY FAILED".mysqli_error());
                    }
                 header("location:category.php");
             }

      }
    }
    function delcat()
    {
      global $connection;
      $catid=$_GET['cid'];
      $query="Delete from categorys where catid='$catid'";
      $go_query=mysqli_query($connection,$query);
      header("location:category.php");
    }
    function updatecat()
    {
    global $connection;
    $catname=$_POST['updatecategory'];
    $catid=$_GET['cid'];
    $query="update categorys set catname='$catname' where catid='$catid'";
    $go_query=mysqli_query($connection,$query);
    if(!$go_query)
    {
        die("Query Failed".mysqli_error($connection));
    }
    header("location:category.php");

    }

function addproduct()
{
    global $connection;
    $productname=$_POST['txtproductname'];//textbox name
    $categoryid=$_POST['category'];//select name
    $authorid=$_POST['author'];//select name
    $description=$_POST['desc'];//select name
    $price=$_POST['price'];//textbox name
    $quantity=$_POST['qty'];//textbox name
    $photo=$_FILES['photo']['name'];//file name
    if(is_numeric($price)==false)
    {
        echo "<script>window.alert('Please Enter Price is numeric value')</script>";
    }
    elseif(is_numeric($quantity)==false)
    {
        echo "<script>window.alert('Please Enter Product Quantity is numeric value')</script>";
    }
    elseif($photo=="")
    {
        echo "<script>window.alert('Choose Product Images')</script>";
    }
    elseif($productname!="" && $photo!="")
    {
        $query="Select * from products where productname='$productname'";
        $ch_query=mysqli_query($connection,$query);
        $count=mysqli_num_rows($ch_query);
        if($count>0)
        {
            echo"<script>window.alert('This Product is already exists')</script>";
        }
        else
        {
            $query="insert into products(productname,categoryid,authorid,price,Qty,photo,description) values('$productname','$categoryid','$authorid','$price','$quantity','$photo','$description')";
            $go_query=mysqli_query($connection,$query);
            if(!$go_query)
            {
                die("QUERY FAILED".mysqli_error($connection));
            }
            else
            {
                move_uploaded_file($_FILES['photo']['tmp_name'],'../photos/'.$photo);
                header("location:productlist.php");
            }
        }
    }
}

function del_product()
{
    global $connection;
    $pid=$_GET['pid'];
    $query="Delete from products where productid='$pid' ";
    $go_query=mysqli_query($connection,$query);
    header("location:productlist.php");
}
function update_product()
{
    global $connection;
    $pid=$_GET['pid'];
    $productname=$_POST['productname'];
    $categoryid=$_POST['category'];
    $authorid=$_POST['author'];
    $description=$_POST['description'];
    $price=$_POST['price'];
    $quantity=$_POST['qty'];
    $photo=$_FILES['photo']['name'];
    if(!$photo)
    {
        $query="update products set productname='$productname',categoryid='$categoryid',authorid='$authorid',description='$description',price='$price',qty='$quantity' where productid='$pid'";
    }
    else
    {
        $query="update products set productname='$productname',categoryid='$categoryid',authorid='$authorid',description='$description',price='$price',qty='$quantity',photo='$photo' where productid='$pid'";
    }
    $go_query=mysqli_query($connection,$query);
    if(!$go_query)
    {
        die("QUERY FAILED".mysqli_error($connection));
    }
    else
    {
        move_uploaded_file($_FILES['photos']['tmp_name'],'../Photo/'.$photo);
    }
    header("location:productlist.php");
}

function addauthor()
    {
      global $connection;
      $authorname=$_POST['authorname'];

      if(empty($authorname))
      {
        echo '<script>window.alert("Please Enter Author Name")</script>';
      }
      else
      {
        $query="select * from author where authorname='$authorname'";
           $ch_query=mysqli_query($connection,$query);
           $count=mysqli_num_rows($ch_query);
             if($count>0)
             {
                echo"<script>window.alert('This is already exist')</script>";
             }
             else
             {
                            
                $query="insert into author(authorname) values('$authorname')";
                $go_query=mysqli_query($connection,$query);
                if(!$go_query)
                    {
                        die("QUERY FAILED".mysqli_error());
                    }
                 header("location:author.php");
             }

      }
    }
    function delauthor()
    {
      global $connection;
      $authorid=$_GET['aid'];
      $query="Delete from author where authorid='$authorid'";
      $go_query=mysqli_query($connection,$query);
      header("location:author.php");
    }
    function updateauthor()
    {
    global $connection;
    $authorname=$_POST['updateauthor'];
    $authorid=$_GET['aid'];
    $query="update author set authorname='$authorname' where authorid='$authorid'";
    $go_query=mysqli_query($connection,$query);
    if(!$go_query)
    {
        die("Query Failed".mysqli_error($connection));
    }
    header("location:author.php");
   }
   function admin_login()
   {
       global $connection;
       $useraname=$_POST['username'];//textbox name
       $password=$_POST['password'];//textbox name
       $hpass=md5($password);
       $query="Select * from user";
       $go_query=mysqli_query($connection,$query);
       while($out=mysqli_fetch_array($go_query))
       {
           $dbusername=$out['username'];//fieldname
           $dbuserpassword=$out['password'];
           $dbuserrole=$out['role'];
           if($dbusername==$useraname && $dbuserpassword==$hpass && $dbuserrole=='admin')
           {
               $_SESSION['admin']=$useraname;
               header("location:dashboard.php");
           }
           else 
           {
               echo "<script>window.alert('Invalid Admin Name and password')</script>";
               echo "<script>window.location.href='index.php'</script>";
           }
       }
   }
?>

    
