<?php include 'assets/includes/navbar.php'; ?>

<div class="container checkout-page">
    <div class="section-title">
        <h2>Checkout</h2>
        <p>Complete your order</p>
    </div>

    <div class="checkout-grid">
        <!-- Checkout Form -->
        <div class="checkout-form">
            <h3>Delivery Information</h3>
            <form action="place-order.php" method="POST">
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
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
            <div class="checkout-summary-item">
                <span>Best Friends x 2</span>
                <span>Rs. 1,398</span>
            </div>
            <div class="checkout-summary-item">
                <span>Our Story x 1</span>
                <span>Rs. 699</span>
            </div>
            <div class="checkout-summary-item">
                <span>Subtotal</span>
                <span>Rs. 2,097</span>
            </div>
            <div class="checkout-summary-item">
                <span>Shipping</span>
                <span>Rs. 100</span>
            </div>
            <div class="checkout-summary-item total">
                <span>Total</span>
                <span>Rs. 2,197</span>
            </div>
        </div>
    </div>
</div>

<?php include 'assets/includes/footer.php'; ?>
