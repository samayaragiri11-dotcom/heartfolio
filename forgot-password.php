<?php
/*
 * XAMPP can't send email, so instead of an emailed link, a reset request
 * goes to the admin's Messages page. The admin sets a temporary password
 * from Admin > Customers and tells the customer.
 */
require_once 'assets/includes/bootstrap.php';

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Enter the email you signed up with.');
        redirect('forgot-password.php');
    }
    $account = db_one('SELECT id, fullname FROM users WHERE email = ?', [$email]);
    // Only store a request when the account exists, but always show the same
    // message so nobody can use this page to check which emails are registered
    if ($account && !db_value("SELECT COUNT(*) FROM messages WHERE type = 'password_reset' AND user_id = ? AND is_read = 0", [(int)$account['id']])) {
        db_exec("INSERT INTO messages (type, user_id, name, email, subject, message) VALUES ('password_reset', ?, ?, ?, 'Password reset request', ?)",
            [(int)$account['id'], $account['fullname'], $email,
             'This customer asked for a password reset.' . ($phone !== '' ? ' Contact phone: ' . mb_substr($phone, 0, 20) : '')]);
    }
    $sent = true;
}

$pageTitle = 'Reset password';
include 'assets/includes/navbar.php';
?>

<div class="auth-wrap">
    <div class="auth-card">
        <h1>Reset your password</h1>
        <?php if ($sent): ?>
            <div class="alert alert-success" style="margin: 18px 0;"><i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                <span>If that email has an account, our team will contact you with a temporary password, usually within a day.</span></div>
            <a href="login.php" class="btn btn-primary btn-block">Back to log in</a>
        <?php else: ?>
            <p class="sub">Tell us your account email and our team will help you back in.</p>
            <?php echo flash_render(); ?>
            <form method="post">
                <?php echo csrf_field(); ?>
                <div class="field">
                    <label for="email">Account email</label>
                    <input type="email" id="email" name="email" required autocomplete="email">
                </div>
                <div class="field">
                    <label for="phone">Phone <span class="muted">(optional)</span></label>
                    <input type="tel" id="phone" name="phone" placeholder="So we can call you">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Send request</button>
            </form>
            <div class="auth-foot">Remembered it? <a href="login.php">Log in</a></div>
        <?php endif; ?>
    </div>
</div>

<?php include 'assets/includes/footer.php'; ?>
