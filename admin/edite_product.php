<?php
require_once __DIR__ . '/../components/connect.php';
$id = clean_text((string) ($_GET['id'] ?? $_GET['post_id'] ?? ''), 20);
redirect('admin/edit_product.php?id=' . urlencode($id));
