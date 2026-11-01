<?php
require_once dirname(__DIR__) . '/Admin/connect.php';

function show_result(): void
{
    global $connection;
    $data = trim((string) ($_POST['search'] ?? $_GET['search'] ?? ''));
    $result = db_query($connection, 'SELECT * FROM products WHERE productname ILIKE ? ORDER BY productid LIMIT 100', ['%' . $data . '%']);
    if (db_num_rows($result) === 0) { echo '<p>No matching books found.</p>'; }
    while ($book = db_fetch_assoc($result)) {
        echo '<div class="col-md-4"><div class="card"><img class="card-img-top" src="Photos/' . e(rawurlencode($book['photo'])) . '" alt="">';
        echo '<div class="card-body"><h4>' . e($book['productname']) . '</h4><p>' . e($book['price']) . '</p>';
        echo '<a href="product_detail.php?id=' . (int) $book['productid'] . '">View book</a></div></div></div>';
    }
}
function create_accu(): void
{
    global $connection;
    verify_csrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $email = trim((string) ($_POST['email'] ?? ''));
    if (strlen($username) < 3 || strlen($username) > 80 || strlen($password) < 12 || strlen($password) > 72
        || $password !== (string) ($_POST['confirmpassword'] ?? '') || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash_error('Use a username of 3-80 characters, a valid email, and matching passwords of 12-72 bytes.');
        return;
    }
    try {
        db_scalar($connection, 'SELECT register_user(?, ?, ?, ?, ?)', [$username, password_hash($password, PASSWORD_DEFAULT), $email,
            trim((string) ($_POST['phone'] ?? '')), trim((string) ($_POST['address'] ?? ''))]);
    } catch (PDOException $error) {
        if ($error->getCode() !== '23505') { throw $error; }
        flash_error('Unable to register with those details. Try another username.');
        return;
    }
    flash_error('Account created. Please sign in.');
    redirect('login.php');
}
function user_login(): void { authenticate_user('user'); }
function addfeedback(): void
{
    global $connection;
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '' || strlen($message) > 4000) {
        flash_error('Enter your name, a valid email, and a message of at most 4000 bytes.'); return;
    }
    db_query($connection, 'INSERT INTO feedback (name, email, phone, message) VALUES (?, ?, ?, ?)', [$name, $email, trim((string) ($_POST['phone'] ?? '')), $message]);
    flash_error('Thank you. Your feedback has been submitted.');
    redirect('contact.php');

}
