<?php
session_start();
include '../assets/includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['email'] != 'admin@heartfolio.com') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $pages = $_POST['pages'];
    $size = $_POST['size'];
    $paper = $_POST['paper'];
    $description = $_POST['description'];
    
    // Handle image upload
    $image = $_FILES['image']['name'];
    $target_dir = "../assets/images/";
    $target_file = $target_dir . basename($image);
    
    // In real app, you would validate the image file
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        // Insert product
        $stmt = $conn->prepare("INSERT INTO products (name, category, price, stock, pages, size, paper, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiisss", $name, $category, $price, $stock, $pages, $size, $paper, $description, $image);
        
        if ($stmt->execute()) {
            header("Location: products.php?success=Product added successfully");
            exit();
        } else {
            header("Location: add-product.php?error=Failed to add product");
            exit();
        }
    } else {
        header("Location: add-product.php?error=Failed to upload image");
        exit();
    }
    
    $stmt->close();
    $conn->close();
}
?>
