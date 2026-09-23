<?php 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'assets/includes/navbar.php'; 
?>

<div class="container account-page">
    <div class="account-grid">
        <!-- Sidebar -->
        <aside class="account-sidebar">
            <ul>
                <li><a href="#" class="active">Profile</a></li>
                <li><a href="#">Order History</a></li>
                <li><a href="#">Addresses</a></li>
                <li><a href="#">Settings</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Content -->
        <main class="account-content">
            <h2>My Profile</h2>
            <div class="profile-info">
                <p><strong>Name:</strong> John Doe</p>
                <p><strong>Email:</strong> john@example.com</p>
                <p><strong>Phone:</strong> +977 9876543210</p>
                <p><strong>Address:</strong> Kathmandu, Nepal</p>
            </div>

            <h2 style="margin-top: 40px;">Order History</h2>
            <table class="order-history-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#HF-2024-001</td>
                        <td>Sep 22, 2024</td>
                        <td>Rs. 2,197</td>
                        <td><span class="status pending">Pending</span></td>
                        <td><button class="action-btn view-btn">View</button></td>
                    </tr>
                    <tr>
                        <td>#HF-2024-002</td>
                        <td>Sep 15, 2024</td>
                        <td>Rs. 699</td>
                        <td><span class="status delivered">Delivered</span></td>
                        <td><button class="action-btn view-btn">View</button></td>
                    </tr>
                    <tr>
                        <td>#HF-2024-003</td>
                        <td>Sep 10, 2024</td>
                        <td>Rs. 1,398</td>
                        <td><span class="status shipped">Shipped</span></td>
                        <td><button class="action-btn view-btn">View</button></td>
                    </tr>
                </tbody>
            </table>
        </main>
    </div>
</div>

<?php include 'assets/includes/footer.php'; ?>
