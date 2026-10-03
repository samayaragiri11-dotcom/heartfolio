<?php
/*
 * Put this at the very top of every admin page:
 *     require_once __DIR__ . '/../assets/includes/admin-guard.php';
 *
 * It lets the page load only for a logged-in user whose role is "admin".
 * The role is checked in the database on every page, so if an admin is
 * changed back to a customer, they lose access straight away.
 * It also gives the page a ready database connection in $conn.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?error=' . urlencode('Log in to open the admin panel'));
    exit();
}

$guardStmt = $conn->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
$guardStmt->bind_param('i', $_SESSION['user_id']);
$guardStmt->execute();
$guardUser = $guardStmt->get_result()->fetch_assoc();
$guardStmt->close();

if (!$guardUser || $guardUser['role'] !== 'admin') {
    // Logged in, but not an admin: send them to the shop
    header('Location: ../index.php');
    exit();
}

$_SESSION['role'] = 'admin';
unset($guardStmt, $guardUser);
