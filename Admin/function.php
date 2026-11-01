<?php
require_once __DIR__ . '/connect.php';

function admin_login(): void { authenticate_user('admin'); }
function admin_mutation(): void { require_admin(); verify_csrf(); }
function positive_id($value): int
{
    if (!ctype_digit((string) $value) || (int) $value < 1) {
        return 0;
    }
    return (int) $value;
}
function adduser(): void
{
    global $connection;
    admin_mutation();
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['cpassword'] ?? $_POST['confirmpassword'] ?? '');
    $email = trim((string) ($_POST['email'] ?? ''));
    if (strlen($password) < 12 || strlen($password) > 72 || $password !== $confirm || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash_error('Enter a valid email and matching passwords of 12-72 bytes.'); return;
    }
    db_scalar($connection, 'SELECT admin_create_user(?, ?, ?, ?, ?, ?)', [trim((string) ($_POST['username'] ?? '')),
        password_hash($password, PASSWORD_DEFAULT), $email, trim((string) ($_POST['phone'] ?? '')),
        trim((string) ($_POST['address'] ?? '')), (string) ($_POST['role'] ?? $_POST['usertype'] ?? 'user')]);
    redirect('userlist.php');
}
function deluser(): void
{
    global $connection;
    admin_mutation();
    db_scalar($connection, 'SELECT admin_delete_user(?)', [positive_id($_POST['uid'] ?? '')]);
    redirect('userlist.php');
}
function addcat(): void
{
    global $connection;
    admin_mutation();
    db_query($connection, 'INSERT INTO categorys (catname) VALUES (?)', [trim((string) ($_POST['catname'] ?? ''))]);
    redirect('category.php');
}
function delcat(): void
{
    global $connection;
    admin_mutation();
    db_query($connection, 'DELETE FROM categorys WHERE catid = ?', [positive_id($_POST['cid'] ?? '')]);
    redirect('category.php');
}
function updatecat(): void
{
    global $connection;
    admin_mutation();
    db_query($connection, 'UPDATE categorys SET catname = ? WHERE catid = ?', [trim((string) ($_POST['updatecategory'] ?? '')), positive_id($_POST['cid'] ?? $_GET['cid'] ?? '')]);
    redirect('category.php');
}
function addauthor(): void
{
    global $connection;
    admin_mutation();
    db_query($connection, 'INSERT INTO author (authorname) VALUES (?)', [trim((string) ($_POST['authorname'] ?? ''))]);
    redirect('author.php');
}
function delauthor(): void
{
    global $connection;
    admin_mutation();
    db_query($connection, 'DELETE FROM author WHERE authorid = ?', [positive_id($_POST['aid'] ?? '')]);
    redirect('author.php');
}
function updateauthor(): void
{
    global $connection;
    admin_mutation();
    db_query($connection, 'UPDATE author SET authorname = ? WHERE authorid = ?', [trim((string) ($_POST['updateauthor'] ?? '')), positive_id($_POST['aid'] ?? $_GET['aid'] ?? '')]);
    redirect('author.php');
}
function product_photo(): ?string
{
    $upload = $_FILES['photo'] ?? null;
    if (!$upload || $upload['error'] === UPLOAD_ERR_NO_FILE) { return null; }
    if ($upload['error'] !== UPLOAD_ERR_OK || $upload['size'] > 5 * 1024 * 1024 || !is_uploaded_file($upload['tmp_name'])) {
        throw new InvalidArgumentException('Upload a valid image smaller than 5 MB.');
    }
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
    if (!isset($types[$mime]) || !getimagesize($upload['tmp_name'])) { throw new InvalidArgumentException('Unsupported image format.'); }
    $filename = bin2hex(random_bytes(16)) . '.' . $types[$mime];
    if (!move_uploaded_file($upload['tmp_name'], dirname(__DIR__) . '/Photos/' . $filename)) { throw new RuntimeException('Unable to save image.'); }
    return $filename;
}
function product_values(bool $new): array
{
    $name = trim((string) ($_POST[$new ? 'txtproductname' : 'productname'] ?? ''));
    $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $qty = filter_var($_POST['qty'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($name === '' || $price === false || $qty === false) { throw new InvalidArgumentException('Enter a name and nonnegative integer price and stock.'); }
    return [$name, positive_id($_POST['category'] ?? ''), positive_id($_POST['author'] ?? ''), $price, $qty, trim((string) ($_POST[$new ? 'desc' : 'description'] ?? ''))];
}
function addproduct(): void
{
    global $connection;
    admin_mutation();
    $values = product_values(true);
    $values[] = product_photo() ?? '';
    db_query($connection, 'INSERT INTO products (productname, categoryid, authorid, price, qty, description, photo) VALUES (?, ?, ?, ?, ?, ?, ?)', $values);
    redirect('productlist.php');
}
function update_product(): void
{
    global $connection;
    admin_mutation();
    $values = product_values(false);
    $photo = product_photo();
    $values[] = $photo;
    $values[] = positive_id($_POST['pid'] ?? $_GET['pid'] ?? '');
    db_query($connection, 'UPDATE products SET productname=?, categoryid=?, authorid=?, price=?, qty=?, description=?, photo=COALESCE(?,photo) WHERE productid=?', $values);
    redirect('productlist.php');
}
function del_product(): void
{
    global $connection;
    admin_mutation();
    db_query($connection, 'DELETE FROM products WHERE productid=?', [positive_id($_POST['pid'] ?? '')]);
    redirect('productlist.php');
}
