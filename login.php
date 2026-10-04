<?php
require_once 'assets/includes/bootstrap.php';

$next = safe_next($_GET['next'] ?? '', '');
if (current_user()) {
    redirect($next ?: (is_admin() ? 'admin/index.php' : 'index.php'));
}
$oldEmail = $_SESSION['old_email'] ?? '';
unset($_SESSION['old_email']);

$pageTitle = 'Log in';
include 'assets/includes/navbar.php';
?>

<div class="auth-wrap">
    <div class="auth-card">
        <img class="logo" src="assets/images/logo.jpg" alt="" onerror="this.style.display='none'">
        <h1>Welcome back</h1>
        <p class="sub">Log in to your Heartfolio account</p>
        <?php echo flash_render(); ?>

        <form action="login_process.php" method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="next" value="<?php echo e($next); ?>">
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo e($oldEmail); ?>" required autocomplete="email" autofocus>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <div class="pw-wrap">
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                    <button type="button" class="pw-toggle" data-pw-toggle aria-label="Show password"><i class="fa-regular fa-eye" aria-hidden="true"></i></button>
                </div>
            </div>
            <div class="field-inline">
                <span></span>
                <a href="forgot-password.php">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Log in</button>
        </form>

        <div class="auth-foot">New to Heartfolio? <a href="signup.php<?php echo $next ? '?next=' . urlencode($next) : ''; ?>">Create an account</a></div>
    </div>
</div>

<?php include 'assets/includes/footer.php'; ?>
