<?php
require_once __DIR__ . '/../components/connect.php';
require_seller();
$id = clean_text((string) ($_GET['id'] ?? ''), 20);
$stmt = $conn->prepare('SELECT p.*, s.name AS seller_name, s.email AS seller_email FROM products p LEFT JOIN sellers s ON s.id = p.seller_id WHERE p.id = ? LIMIT 1');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product || !can_manage_seller_record($conn, (string) $product['seller_id'])) {
    flash('error', 'Product not found or access denied.');
    redirect('admin/view_product.php');
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Product Details | Admin</title><link rel="stylesheet" href="<?= e(app_url('css/admin_style.css')) ?>"></head><body>
<?php include __DIR__ . '/../components/admin_header.php'; ?>
<main class="admin-main"><div class="admin-container"><div class="admin-page-heading"><div><h1><?= e($product['name']) ?></h1><p>Product ID: <?= e($product['id']) ?></p></div><a class="button" href="<?= e(app_url('admin/edit_product.php?id=' . urlencode($product['id']))) ?>">Edit product</a></div><section class="panel"><div class="profile-card" style="grid-template-columns:320px 1fr"><div><img style="width:100%;height:390px;border-radius:18px;object-fit:cover" src="<?= e(uploaded_image_url($product['thumb_one'])) ?>" alt=""></div><div class="info-list"><?php if (is_system_admin($conn)): ?><div><small>Seller</small><?= e($product['seller_name'] ?? 'Unknown') ?> (<?= e($product['seller_email'] ?? '') ?>)</div><?php endif; ?><div><small>Category</small><?= e($product['category']) ?></div><div><small>Price</small><?= e(money($product['price'])) ?></div><div><small>Stock</small><?= (int) $product['stock'] ?></div><div><small>Status</small><span class="badge <?= e($product['status']) ?>"><?= e($product['status']) ?></span></div><div><small>Description</small><?= nl2br(e($product['product_detail'])) ?></div></div></div></section></div></main>
<?php include __DIR__ . '/../components/admin_footer.php'; include __DIR__ . '/../components/alert.php'; ?>
</body></html>
