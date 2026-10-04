<?php
/*
 * Loaded at the top of every page. Starts the session, connects to the
 * database and loads the shared helpers.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
}

define('HF_ROOT', dirname(__DIR__, 2));

require_once HF_ROOT . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/cart-lib.php';
require_once __DIR__ . '/order-lib.php';

// If the tables haven't been created yet, send everyone to the setup page
if (!defined('HF_SETUP') && !hf_is_installed()) {
    redirect(hf_base() . 'setup.php');
}
