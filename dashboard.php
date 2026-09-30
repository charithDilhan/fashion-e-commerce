<?php
require_once __DIR__ . '/components/connect.php';
redirect(!empty($_SESSION['user_id']) ? 'profile.php' : 'login.php');

