<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['email'] != 'admin@heartfolio.com') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Heartfolio Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <h2>Heartfolio Admin</h2>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="products.php" class="active">Products</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="inventory.php">Inventory</a></li>
                <li><a href="sales.php">Sales Records</a></li>
                <li><a href="../index.php">View Website</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <h1>Add New Product</h1>
                <a href="products.php" class="btn btn-secondary">Back to Products</a>
            </div>

            <div class="admin-form">
                <form action="add-product-process.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="friendship">Friendship</option>
                            <option value="birthday">Birthday</option>
                            <option value="love">Love & Couple</option>
                            <option value="family">Family</option>
                            <option value="anniversary">Anniversary</option>
                            <option value="travel">Travel</option>
                            <option value="others">Others</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="price">Price (Rs.)</label>
                        <input type="number" id="price" name="price" required>
                    </div>
                    <div class="form-group">
                        <label for="stock">Stock Quantity</label>
                        <input type="number" id="stock" name="stock" required>
                    </div>
                    <div class="form-group">
                        <label for="pages">Number of Pages</label>
                        <input type="number" id="pages" name="pages" required>
                    </div>
                    <div class="form-group">
                        <label for="size">Size</label>
                        <input type="text" id="size" name="size" placeholder="e.g., 8.5 x 11" required>
                    </div>
                    <div class="form-group">
                        <label for="paper">Paper Type</label>
                        <input type="text" id="paper" name="paper" placeholder="e.g., Premium Glossy" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="image">Product Image</label>
                        <input type="file" id="image" name="image" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                    <a href="products.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
