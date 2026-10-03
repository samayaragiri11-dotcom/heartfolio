<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'assets/includes/navbar.php';
?>

<div class="container order-confirmation">
    <div id="order-found" style="display: none;">
        <div class="success-icon" id="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1 id="order-heading">Order Placed Successfully!</h1>
        <p id="order-subheading">Thank you for your purchase. Your order has been received.</p>

        <div class="order-details">
            <p><strong>Order ID:</strong> <span id="order-id"></span></p>
            <p><strong>Order Date:</strong> <span id="order-date"></span></p>
            <p><strong>Order Status:</strong> <span class="status pending" id="order-status"></span></p>
            <p><strong>Payment:</strong> <span id="order-payment"></span></p>
            <p><strong>Deliver to:</strong> <span id="order-address"></span></p>
            <hr style="margin: 15px 0; border: none; border-top: 1px solid var(--sand);">
            <p><strong>Products:</strong></p>
            <ul id="order-items" style="margin-left: 20px; color: var(--text-light);"></ul>
            <hr style="margin: 15px 0; border: none; border-top: 1px solid var(--sand);">
            <p><strong>Subtotal:</strong> <span id="order-subtotal"></span></p>
            <p><strong>Shipping:</strong> <span id="order-shipping"></span></p>
            <p><strong>Total Amount:</strong> <span id="order-total"></span></p>
        </div>

        <a href="account.php" class="btn btn-primary">View My Orders</a>
    </div>

    <div id="order-missing" style="display: none; text-align: center;">
        <h1>Order not found</h1>
        <p style="margin: 10px 0 20px;">Check the link, or find the order in your account.</p>
        <a href="account.php" class="btn btn-primary">Go to My Orders</a>
    </div>
</div>

<script src="assets/js/photo-store.js"></script>
<script>
(function () {
    var params = new URLSearchParams(window.location.search);
    var order = HFCart.getOrder(params.get('order') || '');

    if (!order) {
        document.getElementById('order-missing').style.display = 'block';
        return;
    }

    var esc = HFCart.escapeHtml;
    var money = HFCart.formatPrice;
    var paymentNames = { cod: 'Cash on Delivery', esewa: 'eSewa', khalti: 'Khalti' };

    // Just placed vs. opened later from the account page
    if (params.get('new') !== '1') {
        document.getElementById('success-icon').style.display = 'none';
        document.getElementById('order-heading').textContent = 'Order Details';
        document.getElementById('order-subheading').textContent = 'Here is a summary of this order.';
    }

    document.getElementById('order-id').textContent = '#' + order.id;
    document.getElementById('order-date').textContent = HFCart.formatDate(order.date);
    document.getElementById('order-status').textContent = order.status;
    document.getElementById('order-payment').textContent = paymentNames[order.customer.payment] || order.customer.payment;
    document.getElementById('order-address').textContent = order.customer.address + ', ' + order.customer.city;

    document.getElementById('order-items').innerHTML = order.items.map(function (item) {
        var c = item.custom;
        var extra = '';
        if (c) {
            extra = '<div style="display: flex; gap: 12px; margin: 8px 0 12px;">' +
                '<img data-photo-key="' + esc(c.photoKey) + '" alt="Your cover photo" ' +
                    'style="width: 60px; height: 78px; object-fit: cover; border-radius: 4px; background: var(--warm-beige, #F1E6D6);">' +
                '<div style="font-size: 13px;">' +
                    '<div><strong>Title:</strong> ' + esc(c.title || item.name) + '</div>' +
                    (c.names ? '<div><strong>Names:</strong> ' + esc(c.names) + '</div>' : '') +
                    (c.message ? '<div><strong>Message:</strong> ' + esc(c.message) + '</div>' : '') +
                    '<div><strong>Photos:</strong> 1 cover + ' + (c.pageCount || 0) + ' inside</div>' +
                '</div>' +
            '</div>';
        }
        return '<li>' + esc(item.name) + ' x ' + item.qty + ' - ' + money(item.price * item.qty) + extra + '</li>';
    }).join('');

    // Fill in each customized item's cover photo from browser storage
    document.querySelectorAll('#order-items img[data-photo-key]').forEach(function (img) {
        HFPhotos.get(img.getAttribute('data-photo-key')).then(function (data) {
            if (data && data.cover) img.src = URL.createObjectURL(data.cover);
        }).catch(function () {});
    });

    document.getElementById('order-subtotal').textContent = money(order.totals.subtotal);
    document.getElementById('order-shipping').textContent = money(order.totals.shipping);
    document.getElementById('order-total').textContent = money(order.totals.total);

    document.getElementById('order-found').style.display = 'block';
})();
</script>

<?php include 'assets/includes/footer.php'; ?>
