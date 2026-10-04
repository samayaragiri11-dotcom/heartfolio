<?php
/*
 * Database connection. Settings live in config.php.
 */
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    $unknownDb = $e->getCode() === 1049;
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Heartfolio</title></head>'
       . '<body style="font-family:system-ui,sans-serif;max-width:560px;margin:80px auto;padding:0 20px;color:#3F352D">'
       . '<h1 style="font-size:24px">Heartfolio can\'t reach its database</h1>';
    if ($unknownDb) {
        echo '<p>The <strong>heartfolio</strong> database doesn\'t exist yet.</p>'
           . '<p><a href="' . (strpos($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') !== false ? '../' : '') . 'setup.php">Run the one-click setup</a> to create it.</p>';
    } else {
        echo '<p>Check that <strong>MySQL is running</strong> in the XAMPP Control Panel, then refresh this page.</p>';
    }
    echo '</body></html>';
    error_log('Heartfolio DB connection failed: ' . $e->getMessage());
    exit();
}
