<?php
require_once 'assets/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('signup.php');
}
csrf_check();

$fullname = trim($_POST['fullname'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$password = (string)($_POST['password'] ?? '');
$confirm  = (string)($_POST['confirm_password'] ?? '');
$next     = safe_next($_POST['next'] ?? '', '');
$back     = 'signup.php' . ($next ? '?next=' . urlencode($next) : '');

$_SESSION['old_signup'] = ['fullname' => $fullname, 'email' => $email, 'phone' => $phone];

$error = null;
if ($fullname === '' || $email === '' || $phone === '' || $password === '') {
    $error = 'Please fill in every field.';
} elseif (mb_strlen($fullname) > 100) {
    $error = 'Your name is too long.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Enter a valid email address.';
} elseif (!preg_match('/^[0-9+\- ]{7,20}$/', $phone)) {
    $error = 'Enter a valid phone number.';
} elseif (strlen($password) < 8) {
    $error = 'Your password needs at least 8 characters.';
} elseif ($password !== $confirm) {
    $error = 'The two passwords don\'t match.';
} elseif (db_value('SELECT COUNT(*) FROM users WHERE email = ?', [$email])) {
    $error = 'An account with this email already exists. Try logging in.';
}

if ($error) {
    flash('error', $error);
    redirect($back);
}

$id = db_exec("INSERT INTO users (fullname, email, phone, password, role) VALUES (?, ?, ?, ?, 'customer')",
    [$fullname, $email, $phone, password_hash($password, PASSWORD_DEFAULT)]);

unset($_SESSION['old_signup']);
log_user_in(['id' => $id, 'fullname' => $fullname, 'email' => $email, 'role' => 'customer']);
flash('success', 'Welcome to Heartfolio, ' . $fullname . '!');
redirect($next ?: 'index.php');
