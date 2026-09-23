<?php
// Logged-in home page. Same content as index.php, so we reuse it
// instead of keeping two copies that drift apart.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
include 'index.php';
