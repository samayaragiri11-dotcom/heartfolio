<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up — Heartfolio</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- NAVBAR -->
<nav style="background:#fff; padding:15px 20px; border-bottom:1px solid #f2ecf0; text-align:center;">
    <a href="index.php" style="font-weight:700; color:#e8536f; text-decoration:none; font-size:1.1rem;">
        Heartfolio
    </a>
</nav>

<!-- SIGNUP FORM -->
<div class="container">
    <div class="form-container">
        <img src="logo.jpg" alt="Heartfolio">
        <h2>Create Your Account</h2>
        <p>Join Heartfolio today</p>

        <?php if (!empty($_GET['error'])): ?>
            <div class="alert-error">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form id="signupForm" action="signup-process.php" method="POST">
            <div class="form-group">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" placeholder="Jane Doe" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="98XXXXXXXX" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary">Sign Up</button>
        </form>

        <div class="form-footer">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer style="text-align:center; padding:30px 20px; color:#7a7280; font-size:0.9rem;">
    &copy; <?php echo date('Y'); ?> Heartfolio
</footer>

</body>
</html>