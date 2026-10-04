<?php
// Search lives on the shop page now. Kept so old links keep working.
$q = trim($_GET['q'] ?? ($_GET['search'] ?? ''));
header('Location: shop.php' . ($q !== '' ? '?q=' . urlencode($q) : ''));
exit();
