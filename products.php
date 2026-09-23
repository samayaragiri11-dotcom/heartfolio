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
    <title>Product Management - Heartfolio Admin</title>
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
                <h1>Product Management</h1>
                <a href="add-product.php" class="btn btn-primary">Add New Product</a>
            </div>

            <!-- Products Table -->
            <div class="dashboard-section">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td><img src="../assets/images/friendship-1.jpg" alt="Product" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                            <td>Best Friends</td>
                            <td>Friendship</td>
                            <td>Rs. 699</td>
                            <td>25</td>
                            <td>
                                <button class="action-btn edit-btn"><i class="fas fa-edit"></i></button>
                                <button class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td><img src="../assets/images/friendship-2.jpg" alt="Product" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                            <td>Friendship Forever</td>
                            <td>Friendship</td>
                            <td>Rs. 699</td>
                            <td>18</td>
                            <td>
                                <button class="action-btn edit-btn"><i class="fas fa-edit"></i></button>
                                <button class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td><img src="../assets/images/love-1.jpg" alt="Product" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                            <td>Our Story</td>
                            <td>Love & Couple</td>
                            <td>Rs. 699</td>
                            <td>30</td>
                            <td>
                                <button class="action-btn edit-btn"><i class="fas fa-edit"></i></button>
                                <button class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td><img src="../assets/images/birthday-1.jpg" alt="Product" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                            <td>Birthday Special</td>
                            <td>Birthday</td>
                            <td>Rs. 699</td>
                            <td>22</td>
                            <td>
                                <button class="action-btn edit-btn"><i class="fas fa-edit"></i></button>
                                <button class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td><img src="../assets/images/family-1.jpg" alt="Product" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                            <td>Family Memories</td>
                            <td>Family</td>
                            <td>Rs. 699</td>
                            <td>15</td>
                            <td>
                                <button class="action-btn edit-btn"><i class="fas fa-edit"></i></button>
                                <button class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
