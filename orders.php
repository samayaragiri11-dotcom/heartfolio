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
    <title>Order Management - Heartfolio Admin</title>
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
                <li><a href="products.php">Products</a></li>
                <li><a href="orders.php" class="active">Orders</a></li>
                <li><a href="inventory.php">Inventory</a></li>
                <li><a href="sales.php">Sales Records</a></li>
                <li><a href="../index.php">View Website</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <h1>Order Management</h1>
            </div>

            <!-- Orders Table -->
            <div class="dashboard-section">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#HF-2024-001</td>
                            <td>John Doe</td>
                            <td>Sep 22, 2024</td>
                            <td>Rs. 2,197</td>
                            <td><span class="status pending">Pending</span></td>
                            <td>
                                <button class="action-btn view-btn">View</button>
                                <button class="action-btn edit-btn">Update</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#HF-2024-002</td>
                            <td>Jane Smith</td>
                            <td>Sep 21, 2024</td>
                            <td>Rs. 699</td>
                            <td><span class="status processing">Processing</span></td>
                            <td>
                                <button class="action-btn view-btn">View</button>
                                <button class="action-btn edit-btn">Update</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#HF-2024-003</td>
                            <td>Bob Wilson</td>
                            <td>Sep 20, 2024</td>
                            <td>Rs. 1,398</td>
                            <td><span class="status shipped">Shipped</span></td>
                            <td>
                                <button class="action-btn view-btn">View</button>
                                <button class="action-btn edit-btn">Update</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#HF-2024-004</td>
                            <td>Alice Brown</td>
                            <td>Sep 19, 2024</td>
                            <td>Rs. 699</td>
                            <td><span class="status delivered">Delivered</span></td>
                            <td>
                                <button class="action-btn view-btn">View</button>
                                <button class="action-btn edit-btn">Update</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#HF-2024-005</td>
                            <td>Charlie Davis</td>
                            <td>Sep 18, 2024</td>
                            <td>Rs. 2,097</td>
                            <td><span class="status cancelled">Cancelled</span></td>
                            <td>
                                <button class="action-btn view-btn">View</button>
                                <button class="action-btn edit-btn">Update</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
