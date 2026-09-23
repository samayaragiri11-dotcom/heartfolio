<?php
session_start();
include 'assets/includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $payment_method = $_POST['payment_method'];
    
    // Generate unique order ID
    $order_id = 'HF-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Calculate total (in real app, this would come from cart)
    $total_amount = 2197.00;
    
    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (order_id, user_id, fullname, phone, email, address, city, total_amount, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("sisssssds", $order_id, $user_id, $fullname, $phone, $email, $address, $city, $total_amount, $payment_method);
    
    if ($stmt->execute()) {
        // In real app, insert order items here
        header("Location: order-confirmation.php?order_id=" . $order_id);
        exit();
    } else {
        $error = "Order placement failed. Please try again.";
        header("Location: checkout.php?error=" . urlencode($error));
        exit();
    }
    
    $stmt->close();
    $conn->close();
}
?>
