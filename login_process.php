<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

function fail(string $msg): void {
    header('Location: login.php?error=' . urlencode($msg));
    exit();
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    fail('Please fill in all fields');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Please enter a valid email address');
}

$stmt = $conn->prepare('SELECT id, fullname, email, password, role FROM users WHERE email = ? LIMIT 1');
if (!$stmt) {
    fail('Something went wrong. Please try again.');
}
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Same message for "no such email" and "wrong password", so nobody can
// find out which emails have accounts
if (!$user || !password_verify($password, $user['password'])) {
    $conn->close();
    fail('Invalid email or password');
}

// New session id after login, so an old session id can't be reused
session_regenerate_id(true);

$_SESSION['user_id']  = $user['id'];
$_SESSION['fullname'] = $user['fullname'];
$_SESSION['email']    = $user['email'];
$_SESSION['role']     = $user['role'];

$conn->close();

if ($user['role'] === 'admin') {
    header('Location: admin/index.php');
} else {
    header('Location: index.php');
}
exit();
