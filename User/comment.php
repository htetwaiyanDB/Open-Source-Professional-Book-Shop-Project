<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
require_login();
verify_csrf();
$productId = filter_var($_POST['productid'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$content = trim((string) ($_POST['content'] ?? ''));
if (!$productId || $content === '' || strlen($content) > 2000) {
    flash_error('Please enter a comment of at most 2000 characters for an existing book.');
    redirect('userindex.php');
}
db_execute($connection, 'INSERT INTO comments (productid, userid, content) VALUES (?, ?, ?)', [$productId, (int) $_SESSION['user_id'], $content]);
redirect('product_detail.php?id=' . $productId);
