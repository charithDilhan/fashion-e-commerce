<?php
require_once __DIR__ . '/connect.php';
if (is_post()) {
    unset($_SESSION['user_id']);
    session_regenerate_id(true);
    flash('success', 'You have been logged out.');
}
redirect('login.php');
