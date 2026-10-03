<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'assets/includes/navbar.php';
?>

<style>
.cart-custom-note { font-size: 12px; color: var(--soft-pink, #D98291); margin-top: 4px; }
.cart-custom-note a { font-weight: 600; margin-left: 6px; }
</style>

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

<script src="assets/js/photo-store.js"></script>
<script>
(function () {
    var body = document.getElementById('cart-body');
    var esc = HFCart.escapeHtml;
    var money = HFCart.formatPrice;
    var coverUrls = {}; // photoKey -> object URL of the customer's cover photo

    function rowHtml(item) {
        var custom = item.custom;
        var imgSrc = (custom && coverUrls[custom.photoKey]) || item.image;
        var note = custom
            ? '<p class="cart-custom-note">Customized: ' + esc(HFCart.customSummary(custom)) +
              '<a href="customize.php?id=' + item.id + '&edit=' + encodeURIComponent(item.key) + '">Edit</a></p>'
            : '';

        return '<tr data-key="' + esc(item.key) + '">' +
            '<td>' +
                '<div style="display: flex; align-items: center; gap: 15px;">' +
                    '<img src="' + esc(imgSrc) + '" alt="' + esc(item.name) + ' Magazine" data-photo-key="' + esc(custom ? custom.photoKey : '') + '">' +
                    '<div>' +
                        '<h4>' + esc(item.name) + '</h4>' +
                        '<p style="font-size: 12px; color: var(--text-light);">' + esc(item.category) + '</p>' +
                        note +
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

    // Loads each customized item's cover photo once, then swaps it into the row
    function loadCovers(items) {
        items.forEach(function (item) {
            var key = item.custom && item.custom.photoKey;
            if (!key || coverUrls[key]) return;
            HFPhotos.get(key).then(function (data) {
                if (!data || !data.cover) return;
                coverUrls[key] = URL.createObjectURL(data.cover);
                var img = body.querySelector('img[data-photo-key="' + key + '"]');
                if (img) img.src = coverUrls[key];
            }).catch(function () {});
        });
    }

    function render() {
        var items = HFCart.getItems();
        var isEmpty = items.length === 0;

        document.getElementById('cart-empty').style.display = isEmpty ? 'block' : 'none';
        document.getElementById('cart-table').style.display = isEmpty ? 'none' : '';
        document.getElementById('cart-summary').style.display = isEmpty ? 'none' : '';

        body.innerHTML = items.map(rowHtml).join('');
        loadCovers(items);

        var t = HFCart.totals(items);
        document.getElementById('subtotal').textContent = money(t.subtotal);
        document.getElementById('shipping').textContent = money(t.shipping);
        document.getElementById('total').textContent = money(t.total);
    }

    // One click handler for every button in the table
    body.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-action]');
        if (!btn) return;

        var key = btn.closest('tr').getAttribute('data-key');
        var item = HFCart.getItem(key);
        if (!item) return;

        var action = btn.getAttribute('data-action');
        if (action === 'inc') {
            HFCart.setQty(key, item.qty + 1);
        } else if (action === 'dec') {
            HFCart.setQty(key, item.qty - 1);
        } else if (action === 'remove') {
            var warning = item.custom
                ? 'Remove your customized ' + item.name + '? Its photos will be deleted.'
                : 'Remove ' + item.name + ' from your cart?';
            if (!confirm(warning)) return;
            HFCart.remove(key);
            HFCart.toast(item.name + ' removed from cart');
        }
        render();
    });

    // Message after coming back from the customize page
    var done = new URLSearchParams(window.location.search).get('customized');
    if (done === 'added') HFCart.toast('Customized magazine added to cart');
    if (done === 'updated') HFCart.toast('Your changes are saved');

    window.addEventListener('storage', render);
    render();
})();
</script>

<?php include 'assets/includes/footer.php'; ?>
