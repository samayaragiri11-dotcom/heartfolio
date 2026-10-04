<?php
require_once 'assets/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}
csrf_check();

$email    = trim($_POST['email'] ?? '');
$password = (string)($_POST['password'] ?? '');
$next     = safe_next($_POST['next'] ?? '', '');
$back     = 'login.php' . ($next ? '?next=' . urlencode($next) : '');

$_SESSION['old_email'] = $email;

// Slow down password guessing: after 5 failures, wait a minute
$fails = $_SESSION['login_fails'] ?? ['count' => 0, 'at' => 0];
if ($fails['count'] >= 5 && time() - $fails['at'] < 60) {
    flash('error', 'Too many attempts. Please wait a minute and try again.');
    redirect($back);
}

if ($email === '' || $password === '') {
    flash('error', 'Enter your email and password.');
    redirect($back);
}

$user = db_one('SELECT id, fullname, email, password, role FROM users WHERE email = ? LIMIT 1', [$email]);

// Same message whether the email or the password is wrong, so emails can't be guessed
if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['login_fails'] = ['count' => $fails['count'] + 1, 'at' => time()];
    flash('error', 'That email and password don\'t match.');
    redirect($back);
}

unset($_SESSION['login_fails'], $_SESSION['old_email']);
log_user_in($user);
flash('success', 'Welcome back, ' . $user['fullname'] . '!');

if ($next) {
    redirect($next);
}
redirect($user['role'] === 'admin' ? 'admin/index.php' : 'index.php');
