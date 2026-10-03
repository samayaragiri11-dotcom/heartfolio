<?php
require_once __DIR__ . '/../assets/includes/admin-guard.php';
require_once __DIR__ . '/partials/layout.php';
include_once __DIR__ . '/../assets/includes/products-data.php';

// ---- Numbers for the cards (all real, from the database or catalogue) ----
$totalCustomers = (int)$conn->query(
    "SELECT COUNT(*) FROM users WHERE role = 'customer'"
)->fetch_row()[0];

$newThisMonth = (int)$conn->query(
    "SELECT COUNT(*) FROM users
     WHERE role = 'customer'
       AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
)->fetch_row()[0];

$totalProducts   = count($PRODUCTS);
$totalCategories = count(array_unique(array_column($PRODUCTS, 'category')));

// ---- Newest customers ----
$recent = $conn->query(
    "SELECT fullname, email, phone, created_at
     FROM users
     WHERE role = 'customer'
     ORDER BY created_at DESC
     LIMIT 8"
);

admin_header('Dashboard', 'dashboard');
?>

<div class="admin-header">
    <div>
        <h1>Dashboard</h1>
        <p>Welcome back, <?php echo admin_e($_SESSION['fullname'] ?? 'Admin'); ?>.</p>
    </div>
</div>

<div class="dashboard-cards">
    <div class="dashboard-card">
        <h3>Customers</h3>
        <div class="number"><?php echo $totalCustomers; ?></div>
    </div>
    <div class="dashboard-card">
        <h3>New this month</h3>
        <div class="number"><?php echo $newThisMonth; ?></div>
    </div>
    <div class="dashboard-card">
        <h3>Products</h3>
        <div class="number"><?php echo $totalProducts; ?></div>
        <a href="products.php">Manage products</a>
    </div>
    <div class="dashboard-card">
        <h3>Categories</h3>
        <div class="number"><?php echo $totalCategories; ?></div>
    </div>
</div>

<div class="dashboard-section">
    <h3>Newest customers</h3>
    <?php if ($recent->num_rows === 0): ?>
        <p class="admin-empty">No customers yet. Accounts appear here when people sign up.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $recent->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo admin_e($row['fullname']); ?></td>
                        <td><?php echo admin_e($row['email']); ?></td>
                        <td><?php echo admin_e($row['phone']); ?></td>
                        <td><?php echo admin_e(date('M j, Y', strtotime($row['created_at']))); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
$conn->close();
admin_footer();
