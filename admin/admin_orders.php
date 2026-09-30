<?php
require_once __DIR__ . '/../components/connect.php';
require_seller();
$isSystemAdmin = is_system_admin($conn);

if (is_post() && isset($_POST['update_order'])) {
    $orderId = clean_text((string) ($_POST['order_id'] ?? ''), 20);
    $newStatus = clean_text((string) ($_POST['status'] ?? ''), 30);
    $paymentStatus = clean_text((string) ($_POST['payment_status'] ?? ''), 30);
    $validStatuses = ['in progress', 'preparing', 'delivering', 'delivered', 'confirmed', 'canceled'];
    $validPayments = ['pending', 'paid', 'refunded'];

    if (!in_array($newStatus, $validStatuses, true) || !in_array($paymentStatus, $validPayments, true)) {
        flash('error', 'Invalid order status selected.');
        redirect('admin/admin_orders.php');
    }

    try {
        $conn->beginTransaction();
        $stmt = $conn->prepare('SELECT * FROM orders WHERE id = ? FOR UPDATE');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        if (!$order || !can_manage_seller_record($conn, (string) $order['seller_id'])) {
            throw new RuntimeException('Order not found or access denied.');
        }

        if ($newStatus === 'canceled' && $order['status'] !== 'canceled') {
            $restore = $conn->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
            $restore->execute([(int) $order['qty'], $order['product_id']]);
        } elseif ($newStatus !== 'canceled' && $order['status'] === 'canceled') {
            $deduct = $conn->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
            $deduct->execute([(int) $order['qty'], $order['product_id'], (int) $order['qty']]);
            if ($deduct->rowCount() !== 1) {
                throw new RuntimeException('Not enough stock to reactivate this canceled order.');
            }
        }

        $update = $conn->prepare('UPDATE orders SET status = ?, payment_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $update->execute([$newStatus, $paymentStatus, $orderId]);
        $conn->commit();
        flash('success', 'Order updated successfully.');
    } catch (Throwable $exception) {
        if ($conn->inTransaction()) $conn->rollBack();
        flash('error', friendly_exception($exception, 'The order could not be updated. Please try again.'));
    }
    redirect('admin/admin_orders.php');
}

$allStatuses = ['in progress', 'preparing', 'delivering', 'delivered', 'confirmed','canceled'];
$status = clean_text((string) ($_GET['status'] ?? ''), 30);
$where = [];
$params = [];
if (!$isSystemAdmin) {
    $where[] = 'o.seller_id = ?';
    $params[] = $seller_id;
}
if (in_array($status, $allStatuses, true)) {
    $where[] = 'o.status = ?';
    $params[] = $status;
}
$sql = 'SELECT o.*, p.name AS product_name, p.thumb_one, s.name AS seller_name, s.email AS seller_email FROM orders o LEFT JOIN products p ON p.id = o.product_id LEFT JOIN sellers s ON s.id = o.seller_id';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY o.created_at DESC, o.date DESC';
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Orders | Admin</title><link rel="stylesheet" href="<?= e(app_url('css/admin_style.css')) ?>"></head><body>
<?php include __DIR__ . '/../components/admin_header.php'; ?>
<main class="admin-main"><div class="admin-container"><div class="admin-page-heading"><div><h1>Orders</h1><p><?= $isSystemAdmin ? 'Process orders from every seller account.' : 'Process customer orders and payment states.' ?></p></div></div>
<section class="panel"><form method="get" style="display:flex;gap:10px;margin-bottom:16px"><select name="status" style="padding:10px;border:1px solid var(--line);border-radius:10px"><option value="">All statuses</option><?php foreach ($allStatuses as $option): ?><option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= e(ucwords($option)) ?></option><?php endforeach; ?></select><button class="button" type="submit">Filter</button></form>
<?php if ($orders): ?>
<div class="table-wrap">
  <table class="data-table">
    <thead>
      <tr>
        <th>Order</th>
        <th>Product</th>
        <?php if ($isSystemAdmin): ?><th>Seller</th><?php endif; ?>
        <th>Customer</th>
        <th>Delivery</th>
        <th>Total</th>
        <th>Update</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $order): ?>
      <tr>
        <td>
          <strong>#<?= e($order['id']) ?></strong>
          <small class="text-muted"><?= e(date('M d, Y', strtotime($order['date']))) ?></small>
        </td>
        <td>
          <div class="table-product">
            <img src="<?= e(uploaded_image_url($order['thumb_one'] ?? '')) ?>" alt="Product">
            <div class="table-product-details">
              <strong><?= e($order['product_name'] ?? 'Deleted product') ?></strong>
              <small class="text-muted">Qty: <?= (int) $order['qty'] ?><?= !empty($order['option_size']) ? ' | Size: ' . e($order['option_size']) : '' ?></small>
            </div>
          </div>
        </td>
        <?php if ($isSystemAdmin): ?>
        <td>
          <div class="table-seller">
            <strong><?= e($order['seller_name'] ?? 'Unknown') ?></strong>
            <small class="text-muted"><?= e($order['seller_email'] ?? '') ?></small>
          </div>
        </td>
        <?php endif; ?>
        <td>
          <div class="table-customer">
            <strong><?= e($order['name']) ?></strong>
            <small class="text-muted"><?= e($order['number']) ?></small>
            <small class="text-muted"><?= e($order['email']) ?></small>
          </div>
        </td>
        <td>
          <div class="table-delivery">
            <span><?= e($order['address']) ?></span>
            <small class="text-muted"><?= e($order['method']) ?></small>
          </div>
        </td>
        <td>
          <strong style="white-space:nowrap;"><?= e(money((float) $order['price'] * (int) $order['qty'])) ?></strong>
        </td>
        <td>
          <form method="post" style="display:grid;gap:7px;min-width:150px">
            <?= csrf_field() ?>
            <input type="hidden" name="order_id" value="<?= e($order['id']) ?>">
            <select name="status">
              <?php foreach ($allStatuses as $option): ?>
                <option value="<?= e($option) ?>" <?= $order['status'] === $option ? 'selected' : '' ?>><?= e(ucwords($option)) ?></option>
              <?php endforeach; ?>
            </select>
            <select name="payment_status">
              <?php foreach (['pending','paid','refunded'] as $option): ?>
                <option value="<?= e($option) ?>" <?= $order['payment_status'] === $option ? 'selected' : '' ?>><?= e(ucwords($option)) ?></option>
              <?php endforeach; ?>
            </select>
            <button class="button small" type="submit" name="update_order">Save</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="empty-state">No orders found. Orders will appear after a customer completes checkout.</div>
<?php endif; ?>
</section>
</div>
</main>
<?php include __DIR__ . '/../components/admin_footer.php'; include __DIR__ . '/../components/alert.php'; ?>
</body>
</html>
