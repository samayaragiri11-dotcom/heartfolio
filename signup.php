<?php
require_once 'assets/includes/bootstrap.php';

$next = safe_next($_GET['next'] ?? '', '');
if (current_user()) {
    redirect($next ?: 'index.php');
}
$old = $_SESSION['old_signup'] ?? [];
unset($_SESSION['old_signup']);

$pageTitle = 'Create an account';
include 'assets/includes/navbar.php';
?>

<div class="auth-wrap">
    <div class="auth-card">
        <img class="logo" src="assets/images/logo.jpg" alt="" onerror="this.style.display='none'">
        <h1>Create your account</h1>
        <p class="sub">Save your orders and check out faster</p>
        <?php echo flash_render(); ?>

        <form action="signup-process.php" method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="next" value="<?php echo e($next); ?>">
            <div class="field">
                <label for="fullname">Full name</label>
                <input type="text" id="fullname" name="fullname" value="<?php echo e($old['fullname'] ?? ''); ?>" required maxlength="100" autocomplete="name">
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo e($old['email'] ?? ''); ?>" required maxlength="150" autocomplete="email">
            </div>
            <div class="field">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" value="<?php echo e($old['phone'] ?? ''); ?>" required placeholder="98XXXXXXXX" autocomplete="tel">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <div class="pw-wrap">
                    <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
                    <button type="button" class="pw-toggle" data-pw-toggle aria-label="Show password"><i class="fa-regular fa-eye" aria-hidden="true"></i></button>
                </div>
                <p class="hint">At least 8 characters.</p>
            </div>
            <div class="field">
                <label for="confirm_password">Confirm password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Create account</button>
        </form>

        <div class="auth-foot">Already have an account? <a href="login.php<?php echo $next ? '?next=' . urlencode($next) : ''; ?>">Log in</a></div>
    </div>
</div>

<?php include 'assets/includes/footer.php'; ?>
