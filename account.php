<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$fullname = $_SESSION['fullname'] ?? 'Customer';
$email    = $_SESSION['email'] ?? '';
include 'assets/includes/navbar.php';
?>

<div class="container account-page">
    <div class="account-grid">
        <!-- Sidebar -->
        <aside class="account-sidebar">
            <ul>
                <li><a href="#profile" class="active">Profile</a></li>
                <li><a href="#orders">Order History</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Content -->
        <main class="account-content">
            <h2 id="profile">My Profile</h2>
            <div class="profile-info">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($fullname); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            </div>

            <h2 id="orders" style="margin-top: 40px;">Order History</h2>
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
                <tbody id="orders-body"></tbody>
            </table>
        </main>
    </div>
</div>

<script src="assets/js/cart.js"></script>
<script>
(function () {
    var ACCOUNT_EMAIL = <?php echo json_encode($email, JSON_HEX_TAG | JSON_HEX_AMP); ?>;
    var esc = HFCart.escapeHtml;

    // Only show orders placed while logged in as this user
    var orders = HFCart.getOrders().filter(function (o) {
        return o.account === ACCOUNT_EMAIL;
    });

    var body = document.getElementById('orders-body');
    if (!orders.length) {
        body.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">' +
            'No orders yet. <a href="shop.php">Start shopping</a></td></tr>';
        return;
    }

    body.innerHTML = orders.map(function (o) {
        return '<tr>' +
            '<td>#' + esc(o.id) + '</td>' +
            '<td>' + esc(HFCart.formatDate(o.date)) + '</td>' +
            '<td>' + HFCart.formatPrice(o.totals.total) + '</td>' +
            '<td><span class="status ' + esc(o.status.toLowerCase()) + '">' + esc(o.status) + '</span></td>' +
            '<td><a class="action-btn view-btn" href="order-confirmation.php?order=' + encodeURIComponent(o.id) + '">View</a></td>' +
        '</tr>';
    }).join('');
})();
</script>

<?php include 'assets/includes/footer.php'; ?>
