<?php 
include 'assets/includes/navbar.php'; 
?>

<div class="container">
    <div class="form-container">
        <h2>Forgot Password</h2>
        <p>Reset your password</p>
        <form action="reset-password-process.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit" class="btn btn-primary">Send Reset Link</button>
        </form>
        <div class="form-footer">
            <p>Remember your password? <a href="login.php">Login</a></p>
        </div>
    </div>
</div>

<?php include 'assets/includes/footer.php'; ?>
