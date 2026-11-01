<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
$product_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$product_id) { redirect('userindex.php'); }
$product = db_fetch_assoc(db_query($connection, 'SELECT p.*, a.authorname, COALESCE(d.description, p.description) AS description, COALESCE(d.slug, p.productname) AS slug, d.pages FROM products p LEFT JOIN product_details d ON p.productid = d.product_id LEFT JOIN author a ON a.authorid = p.authorid WHERE p.productid = ?', [$product_id]));
if (!$product) { http_response_code(404); exit('Book not found.'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo e($product['slug']); ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" type="text/css" href="assets/style.css"/>
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<body>
  <?php include 'userheader.php'; ?>
  <div class="container mt-5">
    <div class="d-flex flex-row bd-highlight mb-3">
      <div class="p-3 w-50 bd-highlight">
        <div class="row">
          <div class="col-sm-12">
            <div class="card mb-3">
              <img src="Photos/<?php echo e($product['photo']); ?>" class="card-img-top mt-3" alt="<?php echo e($product['productname']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
          </div>
          <form action="comment.php" method="post">
<?php echo csrf_field(); ?>
              <input type="hidden" name="productid" value="<?php echo isset($product_id) ? $product_id : ''; ?>">
              <textarea name="content" class="form-control mb-2" placeholder="New Comment"></textarea>
              <input type="submit" class="btn btn-primary" value="Add New Comment">
              <a href="#comments" class="btn btn-warning ms-3">View comments</a>
          </form>
          

        </div>
      </div>
      <div class="w-50 p-4 bd-highlight">
        <div class="card-body btn-light">
          <h5 class="card-title">Book's name - <?php echo e($product['productname']); ?></h5>
          <p class="card-text"> Price - <?php echo $product['price']."Ks"; ?></p>
          <p class="card-text">Author - <?php echo e($product['authorname']); ?></p>
          <p class="card-text">Description - <?php echo e($product['description']); ?></p>
          <table class="table table-bordered">
            <tr>
              <td><i>Pages</i></td>
              <td><i><?php echo e($product['pages']); ?></i></td>
            </tr>
            
          </table>
          <form action="addtocart.php" method="post"><?php echo csrf_field(); ?><input type="hidden" name="id" value="<?php echo e($product['productid']); ?>"><button type="submit" class="btn btn-primary w-100">Add to Cart</button></form>
        </div>  
      </div>
    </div>
    <section id="comments" class="mt-4"><h3>Comments</h3>
    <?php $comments = db_query($connection, 'SELECT content FROM comments WHERE productid = ? ORDER BY id DESC', [$product_id]); ?>
    <?php while ($comment = db_fetch_assoc($comments)): ?><p><?php echo e($comment['content']); ?></p><?php endwhile; ?>
    </section>
  </div>
</body>
</html>
