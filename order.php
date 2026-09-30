<?php
require_once __DIR__ . '/components/connect.php';
require_user();
$stmt = $conn->prepare('SELECT o.*, p.name AS product_name, p.thumb_one FROM orders o LEFT JOIN products p ON p.id=o.product_id WHERE o.user_id=? ORDER BY o.created_at DESC, o.date DESC');
$stmt->execute([$user_id]);
$orders=$stmt->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>My Orders | Fashion Store</title><link rel="stylesheet" href="<?= e(app_url('css/user_style.css')) ?>?v=<?= filemtime(app_root_path().'/css/user_style.css') ?>"></head><body>
<?php include __DIR__ . '/components/user_header.php'; ?>
<section class="page-hero"><div class="container"><h1>My orders</h1><p>Track order and payment progress.</p></div></section>
<section class="section"><div class="container"><?php if($orders): ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Product</th><th>Order date</th><th>Quantity</th><th>Total</th><th>Method</th><th>Order status</th><th>Payment</th></tr></thead><tbody><?php foreach($orders as $order): ?><tr><td><div class="table-product"><img src="<?= e(uploaded_image_url($order['thumb_one'] ?? '')) ?>" alt=""><div><strong><?= e($order['product_name'] ?? 'Deleted product') ?></strong><div class="muted">#<?= e($order['id']) ?><?= !empty($order['option_size']) ? ' • Size: ' . e($order['option_size']) : '' ?></div></div></div></td><td><?= e(date('M d, Y',strtotime($order['date']))) ?></td><td><?= (int)$order['qty'] ?></td><td><?= e(money((float)$order['price']*(int)$order['qty'])) ?></td><td><?= e($order['method']) ?></td><td><span class="badge <?= e(str_replace(' ','-',$order['status'])) ?>"><?= e($order['status']) ?></span></td><td><span class="badge <?= e(str_replace(' ','-',$order['payment_status'])) ?>"><?= e($order['payment_status']) ?></span></td></tr><?php endforeach; ?></tbody></table></div><?php else: ?><div class="empty-state"><h3>No orders yet</h3><p>Your completed checkouts will appear here.</p><a class="button" href="<?= e(app_url('shop.php')) ?>">Shop now</a></div><?php endif; ?></div></section>
<?php include __DIR__ . '/components/user_footer.php'; include __DIR__ . '/components/alert.php'; ?>
</body></html>

