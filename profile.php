<?php
require_once __DIR__ . '/components/connect.php';
require_user();
$user = current_user($conn);
$orderStmt = $conn->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
$orderStmt->execute([$user_id]);
$orderCount = (int) $orderStmt->fetchColumn();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>My Profile | Fashion Store</title><link rel="stylesheet" href="<?= e(app_url('css/user_style.css')) ?>?v=<?= filemtime(app_root_path().'/css/user_style.css') ?>"></head><body>
<?php include __DIR__ . '/components/user_header.php'; ?>
<section class="page-hero"><div class="container"><h1>My profile</h1><p>Manage your account and shopping activity.</p></div></section>
<section class="section"><div class="container"><div class="form-card" style="max-width:900px;margin:0 auto"><div class="profile-panel"><img class="profile-photo" src="<?= e(uploaded_image_url($user['image'] ?? '', 'avatar')) ?>" alt="<?= e($user['name'] ?? '') ?>"><div><h2><?= e($user['name'] ?? '') ?></h2><div class="info-list"><div><span>Email</span><?= e($user['email'] ?? '') ?></div><div><span>Total orders</span><?= $orderCount ?></div><div><span>Cart items</span><?= cart_count($conn, $user_id) ?></div><div><span>Wishlist items</span><?= wishlist_count($conn, $user_id) ?></div></div><div class="button-row"><a class="button" href="<?= e(app_url('update_profile.php')) ?>">Edit profile</a><a class="button secondary" href="<?= e(app_url('order.php')) ?>">View orders</a></div></div></div></div></div></section>
<?php include __DIR__ . '/components/user_footer.php'; include __DIR__ . '/components/alert.php'; ?>
</body></html>

