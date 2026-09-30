<?php
require_once __DIR__ . '/connect.php';
if (is_post()) {
    unset($_SESSION['seller_id']);
    session_regenerate_id(true);
    flash('success', 'Admin session ended.');
}
redirect('admin/login.php');
