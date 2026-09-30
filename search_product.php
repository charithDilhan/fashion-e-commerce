<?php
require_once __DIR__ . '/components/connect.php';
$q = clean_text((string) ($_POST['search_product'] ?? $_GET['q'] ?? ''), 100);
redirect('shop.php?q=' . urlencode($q));

