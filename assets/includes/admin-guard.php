<?php
/*
 * First line of every admin page:
 *     require_once __DIR__ . '/../assets/includes/admin-guard.php';
 * Lets the page load only for a logged-in admin. The role is read from the
 * database on every request, so removing someone's admin role takes effect
 * immediately.
 */
require_once __DIR__ . '/bootstrap.php';

$adminUser = current_user();
if (!$adminUser) {
    flash('info', 'Log in with an admin account to open the admin panel.');
    redirect('../login.php?next=' . urlencode('admin/' . basename($_SERVER['SCRIPT_NAME'])));
}
if ($adminUser['role'] !== 'admin') {
    flash('error', 'That page is for Heartfolio staff only.');
    redirect('../index.php');
}

require_once dirname(__DIR__, 2) . '/admin/partials/layout.php';
