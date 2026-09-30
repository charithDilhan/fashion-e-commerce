<?php
require_once __DIR__ . '/../components/connect.php';
require_seller();

function count_for(PDO $conn, string $sql, array $params = []): int
{
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

$isSystemAdmin = is_system_admin($conn);
$scopeSql = $isSystemAdmin ? '' : ' WHERE seller_id = ?';
$scopeParams = $isSystemAdmin ? [] : [$seller_id];
$scopeAnd = $isSystemAdmin ? ' WHERE ' : ' AND ';

$productCount = count_for($conn, 'SELECT COUNT(*) FROM products' . $scopeSql, $scopeParams);
$activeCount = count_for($conn, "SELECT COUNT(*) FROM products" . $scopeSql . $scopeAnd . "status = 'active'", $scopeParams);
$orderCount = count_for($conn, 'SELECT COUNT(*) FROM orders' . $scopeSql, $scopeParams);
$pendingCount = count_for($conn, "SELECT COUNT(*) FROM orders" . $scopeSql . $scopeAnd . "status IN ('in progress','preparing','delivering','confirmed')", $scopeParams);
$customerCount = $isSystemAdmin ? count_for($conn, 'SELECT COUNT(*) FROM users') : 0;
$messageCount = $isSystemAdmin ? count_for($conn, 'SELECT COUNT(*) FROM message') : 0;
$sellerCount = $isSystemAdmin ? count_for($conn, 'SELECT COUNT(*) FROM sellers') : 0;
$cartCount = $isSystemAdmin ? count_for($conn, 'SELECT COUNT(*) FROM cart') : 0;
$wishlistCount = $isSystemAdmin ? count_for($conn, 'SELECT COUNT(*) FROM wishlist') : 0;

$revenueSql = "SELECT COALESCE(SUM(price * qty), 0) FROM orders" . $scopeSql . $scopeAnd . "payment_status = 'paid' AND status <> 'canceled'";
$revenueStmt = $conn->prepare($revenueSql);
$revenueStmt->execute($scopeParams);
$revenue = (float) $revenueStmt->fetchColumn();

$recentSql = 'SELECT o.*, p.name AS product_name, s.name AS seller_name FROM orders o LEFT JOIN products p ON p.id = o.product_id LEFT JOIN sellers s ON s.id = o.seller_id';
$recentParams = [];
if (!$isSystemAdmin) {
    $recentSql .= ' WHERE o.seller_id = ?';
    $recentParams[] = $seller_id;
}
$recentSql .= ' ORDER BY o.created_at DESC, o.date DESC LIMIT 6';
$recent = $conn->prepare($recentSql);
$recent->execute($recentParams);
$recentOrders = $recent->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Dashboard</title><link rel="stylesheet" href="<?= e(app_url('css/admin_style.css')) ?>"></head><body>
<?php include __DIR__ . '/../components/admin_header.php'; ?>
<main class="admin-main"><div class="admin-container">
<div class="admin-page-heading"><div><h1>Dashboard</h1><p><?= $isSystemAdmin ? 'Complete overview of all sellers, products, customers, and orders.' : 'Overview of your products and orders.' ?></p></div><a class="button" href="<?= e(app_url('admin/add_product.php')) ?>">Add product</a></div>
<div class="stats-grid">
    <div class="stat-card"><span><?= $isSystemAdmin ? 'All products' : 'Products' ?></span><strong><?= $productCount ?></strong></div>
    <div class="stat-card"><span>Active products</span><strong><?= $activeCount ?></strong></div>
    <div class="stat-card"><span>Total orders</span><strong><?= $orderCount ?></strong></div>
    <div class="stat-card"><span>Pending orders</span><strong><?= $pendingCount ?></strong></div>
    <div class="stat-card"><span>Paid revenue</span><strong><?= e(money($revenue)) ?></strong></div>
    <?php if ($isSystemAdmin): ?>
        <div class="stat-card"><span>Customers</span><strong><?= $customerCount ?></strong></div>
        <div class="stat-card"><span>Messages</span><strong><?= $messageCount ?></strong></div>
        <div class="stat-card"><span>Sellers</span><strong><?= $sellerCount ?></strong></div>
        <div class="stat-card"><span>Cart records</span><strong><?= $cartCount ?></strong></div>
        <div class="stat-card"><span>Wishlist records</span><strong><?= $wishlistCount ?></strong></div>
    <?php endif; ?>
</div>
<section class="panel"><h2>Recent orders</h2>
<?php if ($recentOrders): ?>
<div class="table-wrap">
  <table class="data-table">
    <thead>
      <tr>
        <th>Order</th>
        <th>Product</th>
        <?php if ($isSystemAdmin): ?><th>Seller</th><?php endif; ?>
        <th>Customer</th>
        <th>Total</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($recentOrders as $order): ?>
      <tr>
        <td><strong>#<?= e($order['id']) ?></strong></td>
        <td>
          <div class="table-product-details">
            <strong><?= e($order['product_name'] ?? 'Deleted product') ?></strong>
          </div>
        </td>
        <?php if ($isSystemAdmin): ?>
        <td>
          <div class="table-seller">
            <strong><?= e($order['seller_name'] ?? 'Unknown seller') ?></strong>
          </div>
        </td>
        <?php endif; ?>
        <td>
          <div class="table-customer">
            <strong><?= e($order['name']) ?></strong>
          </div>
        </td>
        <td><strong style="white-space:nowrap;"><?= e(money((float) $order['price'] * (int) $order['qty'])) ?></strong></td>
        <td><span class="badge <?= e(str_replace(' ', '-', $order['status'])) ?>"><?= e($order['status']) ?></span></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="empty-state">No orders have been placed yet.</div>
<?php endif; ?>
</section></div></main>
<?php include __DIR__ . '/../components/admin_footer.php'; include __DIR__ . '/../components/alert.php'; ?>
</body></html>
