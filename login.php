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
    <title>Login — Heartfolio</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav style="background:#fff; padding:15px 20px; border-bottom:1px solid #f2ecf0; text-align:center;">
    <a href="index.php" style="font-weight:700; color:#e8536f; text-decoration:none; font-size:1.1rem;">
        Heartfolio
    </a>
</nav>

<div class="container">
    <div class="form-container">
        <img src="assets/images/logo.jpg" alt="Heartfolio" onerror="this.style.display='none'">
        <h2>Welcome Back</h2>
        <p>Log in to your account</p>

        <?php if (!empty($_GET['error'])): ?>
            <div class="alert-error">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_GET['success'])): ?>
            <div class="alert-error" style="background:#d4edda; color:#155724; border-color:#c3e6cb;">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <form id="loginForm" action="login_process.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <div class="form-footer">
            <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
        </div>
    </div>
</div>

<footer style="text-align:center; padding:30px 20px; color:#7a7280; font-size:0.9rem;">
    &copy; <?php echo date('Y'); ?> Heartfolio
</footer>

</body>
</html>