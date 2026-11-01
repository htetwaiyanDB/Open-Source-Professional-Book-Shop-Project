<?php
require_once __DIR__ . '/connect.php';
require_admin();


require_once __DIR__ . '/function.php';

if (isset($_GET['pid']) || isset($_POST['pid']))
{
    $pid = (int) ($_POST['pid'] ?? $_GET['pid'] ?? 0);
    $query="Select * from products where productid=?";
    $go_query=db_query($connection,$query, [(int) $pid]);
    if (db_num_rows($go_query) === 0) { http_response_code(404); exit('Book not found.'); }
    while($row=db_fetch_array($go_query))
    {
        $productid=$row['productid'];
        $productname=$row['productname'];
        $product_author_id=$row['authorid'];
        $product_cat_id=$row['categoryid'];
        $description=$row['description'];
        $price=$row['price'];
        $qty=$row['qty'];
        $photo=$row['photo'];
    }
}
if (!isset($productid)) { redirect('productlist.php'); }
if(isset($_POST['btnupdateproduct']))
{
    verify_csrf();
    update_product();
}
?>
<!DOCTYPE html>
<html lang="en">
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
    <div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Welcome Admin,
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
            </h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 offset-3">
            <div class="card">
                <div class="card-header text-center bg-info">Update Book</div>
            </div>
            <div class="card-body">
                <form action="" method="post" enctype="multipart/form-data">
<?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label for="" class="form-label">Product Name</label>
                        <input type="text" name="productname" class="form-control" value="<?php echo e($productname); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="" class="form-label">Author Name</label>
                        <select name="author" id="" class="form-select">
                            <option value="">-----Select One-----</option>
                            <?php
                            $query="Select * from author";
                            $go_query=db_query($connection,$query);
                            while($row=db_fetch_array($go_query))
                            
                            {
                                $authorid=$row['authorid'];
                                $authorname=$row['authorname'];
                                if($product_author_id==$authorid)
                                {
                                    echo "<option value=" . e($authorid) . " selected>" . e($authorname) . "</option>";
                                }
                                else
                                {
                                    echo "<option value=" . e($authorid) . ">" . e($authorname) . "</option>";
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
                            $go_query=db_query($connection,$query);
                            while($row=db_fetch_array($go_query))
                            
                            {
                                $catid=$row['catid'];
                                $catname=$row['catname'];
                                if($product_cat_id==$catid)
                                {
                                    echo "<option value=" . e($catid) . " selected>" . e($catname) . "</option>";
                                }
                                else
                                {
                                    echo "<option value=" . e($catid) . ">" . e($catname) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="" class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" value="<?php echo e($description); ?> " >
                     </div>
                    <div class="mb-3">
                        <label for="" class="form-label">Price</label>
                        <input type="text" name="price" class="form-control" value="<?php echo e($price); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="" class="form-label">Quantity</label>
                        <input type="text" name="qty" class="form-control" value="<?php echo e($qty); ?>">
                    </div>
                    <div class="mb-3">
                    <label for="" class="form-label">File Input</label>
                        <input type="file" name="photo" id="" class="form-control" 
                        value="<?php echo $photo ?>">
                        <img src="../Photos/<?php echo e($photo) ?>" width="100" height="100" alt="" loading="lazy" decoding="async">
                    </div>
                    <div class="mb-3 d-grid">
                        <input type="hidden" name="pid" value="<?php echo (int) $productid; ?>"><input type="submit" value="Update Book" name="btnupdateproduct" class="btn btn-outline-info">
                    </div>
                    

                </form>
            </div>

        </div>

    </div>
    </div>
</body>
</html>
