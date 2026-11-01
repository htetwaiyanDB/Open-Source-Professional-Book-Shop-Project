<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
$category_id = filter_var($_GET['category_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
$search = trim((string) ($_GET['search_query'] ?? ''));
$perpage = 32;
$page = min(1000000, max(1, (int) ($_GET['page'] ?? 1)));
$start = ($page - 1) * $perpage;
$where = ' WHERE 1=1';
$params = [];
if ($search !== '') {
    $where .= ' AND (productname ILIKE ? OR authorname ILIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
if ($category_id) {
    $where .= ' AND categoryid = ?';
    $params[] = $category_id;
}
$from = ' FROM products JOIN author ON products.authorid = author.authorid';
$go_query = db_query($connection, 'SELECT products.*, author.authorname' . $from . $where . ' ORDER BY productid LIMIT ? OFFSET ?', array_merge($params, [$perpage, $start]));
$total_products = (int) db_scalar($connection, 'SELECT COUNT(*)' . $from . $where, $params);
$num = (int) ceil($total_products / $perpage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Books list</title>
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<body>
<?php include 'userheader.php'; ?>
   
  <div class="container ">
  <div class="row">
<div class="col-md-12"><br>
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
        <div class="row">
          <?php
          if (db_num_rows($go_query) > 0) {
          while ($out = db_fetch_array($go_query)) {
            $product_id = $out['productid'];
            $product_name = $out['productname'];
            $authorname= $out['authorname'];
            $price = $out['price'];
            $photo = $out['photo'];
            $Qty = $out ['qty'];
            ?>
            <div class="col-md-3 mb-3">
              <div class="card" style="border:none;">
                <a href="product_detail.php?id=<?php echo e($product_id); ?>" class="ms-2 mt-3">
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
            }
          } else {
              echo "<div class='col-12'><p class='text-center'>No products found.</p></div>";
          }
          ?>

        </div>
      </div>
    </div>
    <ul class="pagination justify-content-center mt-3">
      <?php
     
      for ($i = 1; $i <= $num; $i++) {
        if ($i == $page) 
        {
          echo "<li class='page-item active'><a class='page-link' href='userindex.php?" . e(http_build_query(['page' => $i, 'category_id' => $category_id, 'search_query' => $search])) . "'>{$i}</a></li>";
        } 
        else 
        {
          echo "<li class='page-item'><a class='page-link' href='userindex.php?" . e(http_build_query(['page' => $i, 'category_id' => $category_id, 'search_query' => $search])) . "'>{$i}</a></li>";
        }
      }
      ?>
    </ul>
    </div>
    <?php include 'userfooter.php' ?>

</body>
</html>
