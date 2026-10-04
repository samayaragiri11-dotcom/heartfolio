<?php
// Old address. Orders now live at order.php.
$n = $_GET['n'] ?? ($_GET['order'] ?? '');
header('Location: ' . ($n !== '' ? 'order.php?n=' . urlencode($n) : 'account.php?tab=orders'));
exit();
