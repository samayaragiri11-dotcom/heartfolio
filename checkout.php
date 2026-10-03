<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$prefillName  = $_SESSION['fullname'] ?? '';
$prefillEmail = $_SESSION['email'] ?? '';
include 'assets/includes/navbar.php';
?>

<div class="container checkout-page">
    <div class="section-title">
        <h2>Checkout</h2>
        <p>Complete your order</p>
    </div>

    <div class="checkout-grid">
        <!-- Checkout Form -->
        <div class="checkout-form">
            <h3>Delivery Information</h3>
            <form id="checkout-form">
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($prefillName); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="98XXXXXXXX"
                           pattern="[0-9]{10}" title="Enter a 10-digit phone number" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($prefillEmail); ?>" required>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" required>
                </div>

                <h3 style="margin-top: 30px;">Payment Method</h3>
                <div class="payment-methods">
                    <label>
                        <input type="radio" name="payment_method" value="cod" checked>
                        Cash on Delivery
                    </label>
                    <label>
                        <input type="radio" name="payment_method" value="esewa">
                        eSewa
                    </label>
                    <label>
                        <input type="radio" name="payment_method" value="khalti">
                        Khalti
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">Place Order</button>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="checkout-summary">
            <h3>Order Summary</h3>
            <div id="summary-items"></div>
            <div class="checkout-summary-item">
                <span>Subtotal</span>
                <span id="subtotal">Rs. 0</span>
            </div>
            <div class="checkout-summary-item">
                <span>Shipping</span>
                <span id="shipping">Rs. 0</span>
            </div>
            <div class="checkout-summary-item total">
                <span>Total</span>
                <span id="total">Rs. 0</span>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var ACCOUNT_EMAIL = <?php echo json_encode($prefillEmail, JSON_HEX_TAG | JSON_HEX_AMP); ?>;
    var items = HFCart.getItems();

    // Nothing to check out: send them back to the cart page
    if (!items.length) {
        window.location.replace('cart.php');
        return;
    }

    var esc = HFCart.escapeHtml;
    var money = HFCart.formatPrice;

    document.getElementById('summary-items').innerHTML = items.map(function (item) {
        return '<div class="checkout-summary-item">' +
            '<span>' + esc(item.name) + ' x ' + item.qty +
                (item.custom ? '<br><small style="color: var(--soft-pink, #D98291);">Customized: ' + esc(HFCart.customSummary(item.custom)) + '</small>' : '') +
            '</span>' +
            '<span>' + money(item.price * item.qty) + '</span>' +
        '</div>';
    }).join('');

    var t = HFCart.totals(items);
    document.getElementById('subtotal').textContent = money(t.subtotal);
    document.getElementById('shipping').textContent = money(t.shipping);
    document.getElementById('total').textContent = money(t.total);

    var form = document.getElementById('checkout-form');
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var data = new FormData(form);
        var customer = {
            fullname: String(data.get('fullname') || '').trim(),
            phone: String(data.get('phone') || '').trim(),
            email: String(data.get('email') || '').trim(),
            address: String(data.get('address') || '').trim(),
            city: String(data.get('city') || '').trim(),
            payment: String(data.get('payment_method') || 'cod')
        };

        // "required" still accepts only spaces, so check again after trimming
        if (!customer.fullname || !customer.address || !customer.city) {
            HFCart.toast('Fill in your name, address and city to place the order');
            return;
        }

        var order = HFCart.placeOrder(customer, ACCOUNT_EMAIL);
        if (!order) {
            window.location.href = 'cart.php';
            return;
        }
        window.location.href = 'order-confirmation.php?order=' + encodeURIComponent(order.id) + '&new=1';
    });
})();
</script>

<?php include 'assets/includes/footer.php'; ?>
