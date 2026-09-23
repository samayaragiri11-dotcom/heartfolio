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
    <title>Sales Records - Heartfolio Admin</title>
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
                <li><a href="orders.php">Orders</a></li>
                <li><a href="inventory.php">Inventory</a></li>
                <li><a href="sales.php" class="active">Sales Records</a></li>
                <li><a href="../index.php">View Website</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <h1>Sales Records</h1>
            </div>

            <!-- Sales Summary Cards -->
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <h3>Total Sales</h3>
                    <div class="number">Rs. 1,45,678</div>
                </div>
                <div class="dashboard-card">
                    <h3>Total Orders</h3>
                    <div class="number">156</div>
                </div>
                <div class="dashboard-card">
                    <h3>Average Order Value</h3>
                    <div class="number">Rs. 934</div>
                </div>
                <div class="dashboard-card">
                    <h3>This Month</h3>
                    <div class="number">Rs. 28,450</div>
                </div>
            </div>

            <!-- Sales by Product -->
            <div class="dashboard-sections">
                <div class="dashboard-section">
                    <h3>Sales by Product</h3>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Units Sold</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Best Friends</td>
                                <td>45</td>
                                <td>Rs. 31,455</td>
                            </tr>
                            <tr>
                                <td>Our Story</td>
                                <td>38</td>
                                <td>Rs. 26,562</td>
                            </tr>
                            <tr>
                                <td>Family Memories</td>
                                <td>32</td>
                                <td>Rs. 22,368</td>
                            </tr>
                            <tr>
                                <td>Birthday Special</td>
                                <td>28</td>
                                <td>Rs. 19,572</td>
                            </tr>
                            <tr>
                                <td>Friendship Forever</td>
                                <td>25</td>
                                <td>Rs. 17,475</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Recent Sales -->
                <div class="dashboard-section">
                    <h3>Recent Sales</h3>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#HF-2024-001</td>
                                <td>Rs. 2,197</td>
                                <td>Sep 22, 2024</td>
                            </tr>
                            <tr>
                                <td>#HF-2024-002</td>
                                <td>Rs. 699</td>
                                <td>Sep 21, 2024</td>
                            </tr>
                            <tr>
                                <td>#HF-2024-003</td>
                                <td>Rs. 1,398</td>
                                <td>Sep 20, 2024</td>
                            </tr>
                            <tr>
                                <td>#HF-2024-004</td>
                                <td>Rs. 699</td>
                                <td>Sep 19, 2024</td>
                            </tr>
                            <tr>
                                <td>#HF-2024-005</td>
                                <td>Rs. 2,097</td>
                                <td>Sep 18, 2024</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Monthly Sales Chart -->
            <div class="dashboard-section" style="margin-top: 20px;">
                <h3>Monthly Sales Overview</h3>
                <div style="height: 250px; display: flex; align-items: flex-end; justify-content: space-around; padding: 20px;">
                    <div style="text-align: center;">
                        <div style="height: 80px; width: 50px; background-color: var(--sand); margin: 0 auto;"></div>
                        <p style="margin-top: 10px; font-size: 12px;">Jan</p>
                        <p style="font-size: 11px; color: var(--text-light);">Rs. 12K</p>
                    </div>
                    <div style="text-align: center;">
                        <div style="height: 100px; width: 50px; background-color: var(--sand); margin: 0 auto;"></div>
                        <p style="margin-top: 10px; font-size: 12px;">Feb</p>
                        <p style="font-size: 11px; color: var(--text-light);">Rs. 15K</p>
                    </div>
                    <div style="text-align: center;">
                        <div style="height: 60px; width: 50px; background-color: var(--sand); margin: 0 auto;"></div>
                        <p style="margin-top: 10px; font-size: 12px;">Mar</p>
                        <p style="font-size: 11px; color: var(--text-light);">Rs. 9K</p>
                    </div>
                    <div style="text-align: center;">
                        <div style="height: 140px; width: 50px; background-color: var(--sand); margin: 0 auto;"></div>
                        <p style="margin-top: 10px; font-size: 12px;">Apr</p>
                        <p style="font-size: 11px; color: var(--text-light);">Rs. 21K</p>
                    </div>
                    <div style="text-align: center;">
                        <div style="height: 110px; width: 50px; background-color: var(--sand); margin: 0 auto;"></div>
                        <p style="margin-top: 10px; font-size: 12px;">May</p>
                        <p style="font-size: 11px; color: var(--text-light);">Rs. 16K</p>
                    </div>
                    <div style="text-align: center;">
                        <div style="height: 180px; width: 50px; background-color: var(--soft-pink); margin: 0 auto;"></div>
                        <p style="margin-top: 10px; font-size: 12px;">Jun</p>
                        <p style="font-size: 11px; color: var(--text-light);">Rs. 28K</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
