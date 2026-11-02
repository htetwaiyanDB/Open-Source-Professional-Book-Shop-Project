<?php
require_once dirname(__DIR__) . '/Admin/connect.php';


            require_once __DIR__ . '/userfunction.php';
            ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Page</title>
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>

<body>
    <?php include 'userheader.php' ?>
    <br>
    <div class="container ">
        <div class="row">
            <div class="col-md-12">
                <h3>Welcome ,
                <span class="text-danger">
                    <?php
                        if (!empty($_SESSION['user'])) {
                                    echo e($_SESSION['user']);
                        } else {
                                    $_SESSION['user'] = '';
                                     echo "";
                        }
                    ?>
                    
                    </span>
                </h3>
            </div>
        </div>
        <div class="row">
        <div class="col-sm-12 mt-5">
        <div class="row book-grid">
          
            <?php 
            $sql = "SELECT * FROM products join author on products.authorid = author.authorid  WHERE description = 'Newrelease' ";
            $result = db_query($connection, $sql);

            $counter = 0;

            if (db_num_rows($result) > 0) {
                while ($out = db_fetch_array($result)) {
                    $product_id = $out['productid'];
                    $product_name = $out['productname'];
                    $authorname= $out['authorname'];
                    $price = $out['price'];
                    $photo = $out['photo'];
                    $Qty = $out ['qty'];
                    ?>
                    <div class="col-12 col-sm-6 col-lg-3 mb-4">
                      <div class="card book-catalog-card border-0">
                        <a href="product_detail.php?id=<?php echo e($product_id); ?>">
                          <img src="../Photos/<?php echo e($photo); ?>" class="card-img-top img-fluid" alt="<?php echo e($product_name); ?>">
                        </a>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo e($product_name); ?></h5>
                            <p class="card-text" style="color:red;"><?php echo e($authorname); ?></p>
                            <p class="card-text"><?php echo e($price); ?></p>
                            <p class="card-text">Stocks <?php echo e($Qty); ?></p>
                    <?php if($Qty==0){?>
                      <button type="button" disabled class="btn btn-danger">Out of stock</button>
                    <?php } else { ?>
                      <form action="addtocart.php" method="post"><?php echo csrf_field(); ?><input type="hidden" name="id" value="<?php echo e($product_id); ?>"><button type="submit" class="btn btn-primary">Add to Cart</button></form>
                    <?php } ?>
                  </div>
                      </div>
                    </div> 
            <?php
                    $counter++;
                }
            } else {
                echo "<div class='col-12'><p>No Books are found.</p></div>";
            }

            
            ?>
        </div>
    </div>
 </div>
</div>
</div>
<?php include'userfooter.php' ?>
</body>

</html>
