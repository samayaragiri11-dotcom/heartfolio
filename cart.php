<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'assets/includes/navbar.php';
?>

<div class="container cart-page">
    <div class="section-title">
        <h2>Shopping Cart</h2>
        <p>Review your items</p>
    </div>

    <!-- Shown when the cart is empty -->
    <div id="cart-empty" style="display: none; text-align: center; padding: 40px 20px;">
        <p style="margin-bottom: 20px;">Your cart is empty. Find a magazine you love in the shop.</p>
        <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
    </div>

    <table class="cart-table" id="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="cart-body">
            <!-- Rows are added by JavaScript from the saved cart -->
        </tbody>
    </table>

    <div class="cart-summary" id="cart-summary">
        <h3>Order Summary</h3>
        <div class="cart-summary-row">
            <span>Subtotal</span>
            <span id="subtotal">Rs. 0</span>
        </div>
        <div class="cart-summary-row">
            <span>Shipping</span>
            <span id="shipping">Rs. 0</span>
        </div>
        <div class="cart-summary-row total">
            <span>Total</span>
            <span id="total">Rs. 0</span>
        </div>
        <a href="checkout.php" class="btn btn-primary" style="width: 100%; margin-top: 20px;">Proceed to Checkout</a>
    </div>
</div>

<script src="assets/js/cart.js"></script>
<script>
(function () {
    var body = document.getElementById('cart-body');
    var esc = HFCart.escapeHtml;
    var money = HFCart.formatPrice;

    function rowHtml(item) {
        return '<tr data-id="' + item.id + '">' +
            '<td>' +
                '<div style="display: flex; align-items: center; gap: 15px;">' +
                    '<img src="' + esc(item.image) + '" alt="' + esc(item.name) + ' Magazine">' +
                    '<div>' +
                        '<h4>' + esc(item.name) + '</h4>' +
                        '<p style="font-size: 12px; color: var(--text-light);">' + esc(item.category) + '</p>' +
                    '</div>' +
                '</div>' +
            '</td>' +
            '<td>' + money(item.price) + '</td>' +
            '<td>' +
                '<div class="cart-actions">' +
                    '<button type="button" data-action="dec" aria-label="Decrease quantity"' + (item.qty <= 1 ? ' disabled' : '') + '>-</button>' +
                    '<span>' + item.qty + '</span>' +
                    '<button type="button" data-action="inc" aria-label="Increase quantity">+</button>' +
                '</div>' +
            '</td>' +
            '<td>' + money(item.price * item.qty) + '</td>' +
            '<td><button type="button" class="action-btn delete-btn" data-action="remove">Remove</button></td>' +
        '</tr>';
    }

    function render() {
        var items = HFCart.getItems();
        var isEmpty = items.length === 0;

        document.getElementById('cart-empty').style.display = isEmpty ? 'block' : 'none';
        document.getElementById('cart-table').style.display = isEmpty ? 'none' : '';
        document.getElementById('cart-summary').style.display = isEmpty ? 'none' : '';

        body.innerHTML = items.map(rowHtml).join('');

        var t = HFCart.totals(items);
        document.getElementById('subtotal').textContent = money(t.subtotal);
        document.getElementById('shipping').textContent = money(t.shipping);
        document.getElementById('total').textContent = money(t.total);
    }

    // One click handler for every button in the table
    body.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-action]');
        if (!btn) return;

        var id = parseInt(btn.closest('tr').getAttribute('data-id'), 10);
        var item = HFCart.getItems().find(function (i) { return i.id === id; });
        if (!item) return;

        var action = btn.getAttribute('data-action');
        if (action === 'inc') {
            HFCart.setQty(id, item.qty + 1);
        } else if (action === 'dec') {
            HFCart.setQty(id, item.qty - 1);
        } else if (action === 'remove') {
            if (!confirm('Remove ' + item.name + ' from your cart?')) return;
            HFCart.remove(id);
            HFCart.toast(item.name + ' removed from cart');
        }
        render();
    });

    window.addEventListener('storage', render);
    render();
})();
</script>

<?php include 'assets/includes/footer.php'; ?>
