<?php
include 'connect.php';
    $id=$_GET['id'];
        $status=$_GET['status'];

        if($status==1)
    {
    $query="UPDATE orders SET status=1,senddate=NOW() where orderid=?";
    }
        else
    {
    $query="UPDATE orders SET status=0,senddate=NULL where orderid=?";
    }

    $stmt=mysqli_prepare($connection,$query);

        if(!$stmt)
    {
        echo "Error in preparing statement: ".mysqli_error($connection);
    }
    else
    {
        mysqli_stmt_bind_param($stmt,"i",$id);
            $go_update=mysqli_stmt_execute($stmt);
                if($go_update)
                {
                    header("location:order_mgmt.php");
                }
    else
    {
        echo "Error Updating orders status: ".mysqli_error($connection);
    }
        mysqli_stmt_close($stmt);
    }
mysqli_close($connection);
?>

</body>
</html>