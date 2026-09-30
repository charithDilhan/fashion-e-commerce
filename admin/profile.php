<?php
require_once __DIR__ . '/../components/connect.php';
require_seller();
$seller = current_seller($conn);
$isSystemAdmin = is_system_admin($conn);
if ($isSystemAdmin) {
    $productCount = (int) $conn->query('SELECT COUNT(*) FROM products')->fetchColumn();
    $orderCount = (int) $conn->query('SELECT COUNT(*) FROM orders')->fetchColumn();
} else {
    $productStmt = $conn->prepare('SELECT COUNT(*) FROM products WHERE seller_id = ?');
    $productStmt->execute([$seller_id]);
    $productCount = (int) $productStmt->fetchColumn();
    $orderStmt = $conn->prepare('SELECT COUNT(*) FROM orders WHERE seller_id = ?');
    $orderStmt->execute([$seller_id]);
    $orderCount = (int) $orderStmt->fetchColumn();
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Profile</title><link rel="stylesheet" href="<?= e(app_url('css/admin_style.css')) ?>"></head><body>
<?php include __DIR__ . '/../components/admin_header.php'; ?>
<main class="admin-main"><div class="admin-container"><div class="admin-page-heading"><div><h1>Profile</h1><p><?= $isSystemAdmin ? 'System administrator account and complete store statistics.' : 'Seller account information.' ?></p></div><a class="button" href="<?= e(app_url('admin/update.php')) ?>">Edit profile</a></div><section class="panel"><div class="profile-card"><img src="<?= e(uploaded_image_url($seller['image'] ?? '', 'avatar')) ?>" alt="<?= e($seller['name'] ?? '') ?>"><div><h2><?= e($seller['name'] ?? '') ?></h2><div class="info-list"><div><small>Account type</small><?= $isSystemAdmin ? 'System / Super Admin' : 'Seller' ?></div><div><small>Email</small><?= e($seller['email'] ?? '') ?></div><div><small><?= $isSystemAdmin ? 'All products' : 'Products' ?></small><?= $productCount ?></div><div><small><?= $isSystemAdmin ? 'All orders' : 'Orders' ?></small><?= $orderCount ?></div><div><small>Account created</small><?= e(date('M d, Y', strtotime($seller['created_at'] ?? 'now'))) ?></div></div></div></div></section></div></main>
<?php include __DIR__ . '/../components/admin_footer.php'; include __DIR__ . '/../components/alert.php'; ?>
</body></html>
