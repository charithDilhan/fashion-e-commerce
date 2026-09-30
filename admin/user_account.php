<?php
require_once __DIR__ . '/../components/connect.php';
require_seller();
if (!is_system_admin($conn)) {
    flash('error', 'Access denied.');
    redirect('admin/dashboard.php');
}
if (is_post() && isset($_POST['delete_user'])) {
    $id = clean_text((string) ($_POST['user_id'] ?? ''), 20);
    try {
        $conn->beginTransaction();
        $conn->prepare('DELETE FROM cart WHERE user_id = ?')->execute([$id]);
        $conn->prepare('DELETE FROM wishlist WHERE user_id = ?')->execute([$id]);
        $conn->prepare('UPDATE message SET user_id = NULL WHERE user_id = ?')->execute([$id]);
        $conn->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        $conn->commit();
        flash('success', 'Customer account deleted. Order history was preserved.');
    } catch (Throwable $exception) {
        if ($conn->inTransaction()) $conn->rollBack();
        flash('error', friendly_exception($exception, 'The customer account could not be deleted. Please try again.'));
    }
    redirect('admin/user_account.php');
}
$stmt = $conn->query('SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count FROM users u ORDER BY u.created_at DESC');
$users = $stmt->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Customers | Admin</title><link rel="stylesheet" href="<?= e(app_url('css/admin_style.css')) ?>"></head><body>
<?php include __DIR__ . '/../components/admin_header.php'; ?>
<main class="admin-main"><div class="admin-container"><div class="admin-page-heading"><div><h1>Customers</h1><p>Registered customer accounts and order counts.</p></div></div><section class="panel"><?php if ($users): ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Customer</th><th>Email</th><th>Orders</th><th>Joined</th><th></th></tr></thead><tbody><?php foreach ($users as $user): ?><tr><td><div class="table-product"><img style="border-radius:50%;height:58px" src="<?= e(uploaded_image_url($user['image'], 'avatar')) ?>" alt=""><strong><?= e($user['name']) ?></strong></div></td><td><?= e($user['email']) ?></td><td><?= (int) $user['order_count'] ?></td><td><?= e(date('M d, Y', strtotime($user['created_at']))) ?></td><td><form method="post" onsubmit="return confirm('Delete this customer account?')"><?= csrf_field() ?><input type="hidden" name="user_id" value="<?= e($user['id']) ?>"><button class="button danger small" type="submit" name="delete_user">Delete</button></form></td></tr><?php endforeach; ?></tbody></table></div><?php else: ?><div class="empty-state">No customer accounts found.</div><?php endif; ?></section></div></main>
<?php include __DIR__ . '/../components/admin_footer.php'; include __DIR__ . '/../components/alert.php'; ?>
</body></html>
