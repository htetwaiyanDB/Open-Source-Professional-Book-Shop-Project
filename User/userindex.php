<?php
session_start();
include '../Admin/connect.php';
include 'userfunction.php'; 

$category_id = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int) $_GET['category_id'] : null;
$search = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';

$perpage = 32;
$page = max(1, isset($_GET['page']) ? (int) $_GET['page'] : 1);
$start = ($page - 1) * $perpage;

$filters = [];
$filterTypes = '';
$filterValues = [];

if ($search !== '') {
    $filters[] = '(products.productname LIKE ? OR author.authorname LIKE ?)';
    $searchPattern = '%' . $search . '%';
    $filterTypes .= 'ss';
    $filterValues[] = $searchPattern;
    $filterValues[] = $searchPattern;
}

if ($category_id !== null) {
    $filters[] = 'products.categoryid = ?';
    $filterTypes .= 'i';
    $filterValues[] = $category_id;
}

$whereSql = $filters ? ' WHERE ' . implode(' AND ', $filters) : '';
$fromSql = ' FROM products JOIN author ON products.authorid = author.authorid' . $whereSql;

$countStatement = $connection->prepare('SELECT COUNT(*) AS total' . $fromSql);
if ($filterTypes !== '') {
    $countStatement->bind_param($filterTypes, ...$filterValues);
}
$countStatement->execute();
$total_products = (int) $countStatement->get_result()->fetch_assoc()['total'];
$countStatement->close();
$num = max(1, (int) ceil($total_products / $perpage));

$productSql = 'SELECT products.productid, products.productname, products.price, products.Qty, products.photo, author.authorname'
    . $fromSql . ' ORDER BY products.productid DESC LIMIT ?, ?';
$productStatement = $connection->prepare($productSql);
$productTypes = $filterTypes . 'ii';
$productValues = array_merge($filterValues, [$start, $perpage]);
$productStatement->bind_param($productTypes, ...$productValues);
$productStatement->execute();
$go_query = $productStatement->get_result();
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
                    echo $_SESSION['user'];
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
          if (mysqli_num_rows($go_query) > 0) {
          while ($out = mysqli_fetch_array($go_query)) {
            $product_id = $out['productid'];
            $product_name = $out['productname'];
            $authorname= $out['authorname'];
            $price = $out['price'];
            $photo = $out['photo'];
            $Qty = $out ['Qty'];
            ?>
            <div class="col-md-3 mb-3">
              <div class="card" style="border:none;">
                <a href="product_detail.php?id=<?php echo $product_id; ?>" class="ms-2 mt-3">
                  <img src="../Photos/<?php echo $photo; ?>" class="card-img-top img-fluid" alt="<?php echo $product_name; ?>" loading="lazy" decoding="async">
                </a>
                <div class="card-body">
                  <h5 class="card-title"><?php echo $product_name; ?></h5>
                  <p class="card-text" style="color:red;"><?php echo $authorname; ?></p>
                  <p class="card-text"><?php echo $price; ?></p>
                  <p class="card-text">Stocks <?php echo $Qty; ?></p>
                    <?php if($Qty==0){?>
                      <a href="index.php" class="btn btn-danger">Out of stock</a>
                    <?php } else { ?>
                      <a href="addtocart.php?id=<?php echo $product_id; ?>" class="btn btn-primary">Add to Cart</a>
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
          $pageQuery = http_build_query(array_filter([
              'page' => $i,
              'search_query' => $search,
              'category_id' => $category_id,
          ], static fn($value) => $value !== null && $value !== ''));
          echo "<li class='page-item active'><a class='page-link' href='userindex.php?{$pageQuery}'>{$i}</a></li>";
        } 
        else 
        {
          $pageQuery = http_build_query(array_filter([
              'page' => $i,
              'search_query' => $search,
              'category_id' => $category_id,
          ], static fn($value) => $value !== null && $value !== ''));
          echo "<li class='page-item'><a class='page-link' href='userindex.php?{$pageQuery}'>{$i}</a></li>";
        }
      }
      ?>
    </ul>
    </div>
    <?php include 'userfooter.php' ?>

</body>
</html>
