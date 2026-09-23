<?php include 'assets/includes/navbar.php'; ?>

<div class="container cart-page">
    <div class="section-title">
        <h2>Shopping Cart</h2>
        <p>Review your items</p>
    </div>
    
    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <img src="assets/images/friendship-1.jpg" alt="Best Friends Magazine">
                        <div>
                            <h4>Best Friends</h4>
                            <p style="font-size: 12px; color: var(--text-light);">Friendship</p>
                        </div>
                    </div>
                </td>
                <td>Rs. 699</td>
                <td>
                    <div class="cart-actions">
                        <button onclick="updateQuantity(1, -1)">-</button>
                        <span id="qty-1">2</span>
                        <button onclick="updateQuantity(1, 1)">+</button>
                    </div>
                </td>
                <td id="total-1">Rs. 1,398</td>
                <td>
                    <button class="action-btn delete-btn" onclick="removeItem(1)">Remove</button>
                </td>
            </tr>
            <tr>
                <td>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <img src="assets/images/love-1.jpg" alt="Our Story Magazine">
                        <div>
                            <h4>Our Story</h4>
                            <p style="font-size: 12px; color: var(--text-light);">Love & Couple</p>
                        </div>
                    </div>
                </td>
                <td>Rs. 699</td>
                <td>
                    <div class="cart-actions">
                        <button onclick="updateQuantity(2, -1)">-</button>
                        <span id="qty-2">1</span>
                        <button onclick="updateQuantity(2, 1)">+</button>
                    </div>
                </td>
                <td id="total-2">Rs. 699</td>
                <td>
                    <button class="action-btn delete-btn" onclick="removeItem(2)">Remove</button>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="cart-summary">
        <h3>Order Summary</h3>
        <div class="cart-summary-row">
            <span>Subtotal</span>
            <span id="subtotal">Rs. 2,097</span>
        </div>
        <div class="cart-summary-row">
            <span>Shipping</span>
            <span>Rs. 100</span>
        </div>
        <div class="cart-summary-row total">
            <span>Total</span>
            <span id="total">Rs. 2,197</span>
        </div>
        <a href="checkout.php" class="btn btn-primary" style="width: 100%; margin-top: 20px;">Proceed to Checkout</a>
    </div>
</div>

<script>
function updateQuantity(itemId, change) {
    const qtyElement = document.getElementById('qty-' + itemId);
    const totalElement = document.getElementById('total-' + itemId);
    let quantity = parseInt(qtyElement.textContent);
    const price = 699;
    
    quantity += change;
    if (quantity < 1) quantity = 1;
    
    qtyElement.textContent = quantity;
    totalElement.textContent = 'Rs. ' + (quantity * price).toLocaleString();
    updateSummary();
}

function removeItem(itemId) {
    if (confirm('Are you sure you want to remove this item?')) {
        // In a real application, this would remove the item from the cart
        alert('Item removed from cart');
    }
}

function updateSummary() {
    // Calculate totals based on quantities
    const qty1 = parseInt(document.getElementById('qty-1').textContent);
    const qty2 = parseInt(document.getElementById('qty-2').textContent);
    const subtotal = (qty1 * 699) + (qty2 * 699);
    const total = subtotal + 100;
    
    document.getElementById('subtotal').textContent = 'Rs. ' + subtotal.toLocaleString();
    document.getElementById('total').textContent = 'Rs. ' + total.toLocaleString();
}
</script>

<?php include 'assets/includes/footer.php'; ?>
