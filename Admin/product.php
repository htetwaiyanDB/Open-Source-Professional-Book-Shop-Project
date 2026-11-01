<?php
session_start();
include 'connect.php';
include 'function.php';
if (isset($_POST['btnaddproduct'])) {
    addproduct();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Book</title>
    <link rel="stylesheet" href="../assets/vendor/bootstrap/bootstrap.min.css">
    <script defer src="../assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<body class="bg-light">
<?php include 'header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Add Book</h2>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-7">
            <div class="card">
                <div class="card-header text-center bg-light">Book Information</div>
                <div class="card-body">
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Book Name</label>
                            <input type="text" name="txtproductname" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Author</label>
                            <select name="author" class="form-select">
                                <?php
                                $query = "SELECT * FROM author";
                                $go_query = mysqli_query($connection, $query);
                                while ($row = mysqli_fetch_array($go_query)) {
                                    $authorid = $row['authorid'];
                                    $authorname = $row['authorname'];
                                    echo "<option value='{$authorid}'>{$authorname}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <?php
                                $query = "SELECT * FROM categorys";
                                $go_query = mysqli_query($connection, $query);
                                while ($row = mysqli_fetch_array($go_query)) {
                                    $catid = $row['catid'];
                                    $catname = $row['catname'];
                                    echo "<option value='{$catid}'>{$catname}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="desc" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="text" name="price" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="text" name="qty" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Book Image</label>
                            <input type="file" name="photo" class="form-control">
                        </div>
                        <div class="d-grid">
                            <input type="submit" value="Add Book" name="btnaddproduct" class="btn btn-outline-info">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
