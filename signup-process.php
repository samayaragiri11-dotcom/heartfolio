<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: signup.php');
    exit();
}

function fail(string $msg): void {
    header('Location: signup.php?error=' . urlencode($msg));
    exit();
}

$fullname         = trim($_POST['fullname'] ?? '');
$email            = trim($_POST['email'] ?? '');
$phone            = trim($_POST['phone'] ?? '');
$password         = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if ($fullname === '' || $email === '' || $phone === '' ||
    $password === '' || $confirm_password === '') {
    fail('Please fill in all required fields');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Please enter a valid email address');
}

if (strlen($password) < 6) {
    fail('Password must be at least 6 characters');
}

if ($password !== $confirm_password) {
    fail('Passwords do not match');
}

$check_stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
if (!$check_stmt) {
    fail('Database error: ' . $conn->error);
}
$check_stmt->bind_param('s', $email);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    $check_stmt->close();
    fail('Email already registered');
}
$check_stmt->close();

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    'INSERT INTO users (fullname, email, phone, password) VALUES (?, ?, ?, ?)'
);
if (!$stmt) {
    fail('Registration error: ' . $conn->error);
}
$stmt->bind_param('ssss', $fullname, $email, $phone, $hashed_password);

if (!$stmt->execute()) {
    fail('Registration failed: ' . $stmt->error);
}

$stmt->close();
$conn->close();

/* NO auto-login — send them to login.php with a success message */
header('Location: login.php?success=' . urlencode('Account created! Please log in.'));
exit();