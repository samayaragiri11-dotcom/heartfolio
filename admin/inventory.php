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
    <title>Inventory Management - Heartfolio Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <img src="../assets/images/logo.jpg" alt="Heartfolio" style="height: 50px; margin-bottom: 20px; border-radius: 50%;">
            <h2>Heartfolio Admin</h2>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="inventory.php" class="active">Inventory</a></li>
                <li><a href="sales.php">Sales Records</a></li>
                <li><a href="../index.php">View Website</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <h1>Inventory Management</h1>
            </div>

            <!-- Inventory Table -->
            <div class="dashboard-section">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Available Stock</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Best Friends</td>
                            <td>Friendship</td>
                            <td>25</td>
                            <td><span class="inventory-status in-stock">In Stock</span></td>
                            <td>
                                <button class="action-btn edit-btn">Update Stock</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Friendship Forever</td>
                            <td>Friendship</td>
                            <td>18</td>
                            <td><span class="inventory-status in-stock">In Stock</span></td>
                            <td>
                                <button class="action-btn edit-btn">Update Stock</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Our Story</td>
                            <td>Love & Couple</td>
                            <td>30</td>
                            <td><span class="inventory-status in-stock">In Stock</span></td>
                            <td>
                                <button class="action-btn edit-btn">Update Stock</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Birthday Special</td>
                            <td>Birthday</td>
                            <td>5</td>
                            <td><span class="inventory-status low-stock">Low Stock</span></td>
                            <td>
                                <button class="action-btn edit-btn">Update Stock</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Family Memories</td>
                            <td>Family</td>
                            <td>15</td>
                            <td><span class="inventory-status in-stock">In Stock</span></td>
                            <td>
                                <button class="action-btn edit-btn">Update Stock</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Anniversary Love</td>
                            <td>Anniversary</td>
                            <td>0</td>
                            <td><span class="inventory-status out-of-stock">Out of Stock</span></td>
                            <td>
                                <button class="action-btn edit-btn">Update Stock</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Travel Adventures</td>
                            <td>Travel</td>
                            <td>22</td>
                            <td><span class="inventory-status in-stock">In Stock</span></td>
                            <td>
                                <button class="action-btn edit-btn">Update Stock</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Special Moments</td>
                            <td>Others</td>
                            <td>8</td>
                            <td><span class="inventory-status low-stock">Low Stock</span></td>
                            <td>
                                <button class="action-btn edit-btn">Update Stock</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
