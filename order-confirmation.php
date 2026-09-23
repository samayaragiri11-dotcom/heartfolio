<?php 
session_start();
include 'assets/includes/navbar.php'; 
?>

<div class="container order-confirmation">
    <div class="success-icon">
        <i class="fas fa-check-circle"></i>
    </div>
    <h1>Order Placed Successfully!</h1>
    <p>Thank you for your purchase. Your order has been received.</p>

    <div class="order-details">
        <p><strong>Order ID:</strong> #HF-2024-001</p>
        <p><strong>Order Date:</strong> September 22, 2024</p>
        <p><strong>Order Status:</strong> <span class="status pending">Pending</span></p>
        <hr style="margin: 15px 0; border: none; border-top: 1px solid var(--sand);">
        <p><strong>Products:</strong></p>
        <ul style="margin-left: 20px; color: var(--text-light);">
            <li>Best Friends x 2 - Rs. 1,398</li>
            <li>Our Story x 1 - Rs. 699</li>
        </ul>
        <hr style="margin: 15px 0; border: none; border-top: 1px solid var(--sand);">
        <p><strong>Subtotal:</strong> Rs. 2,097</p>
        <p><strong>Shipping:</strong> Rs. 100</p>
        <p><strong>Total Amount:</strong> Rs. 2,197</p>
    </div>

    <a href="account.php" class="btn btn-primary">View My Orders</a>
</div>

<?php include 'assets/includes/footer.php'; ?>
