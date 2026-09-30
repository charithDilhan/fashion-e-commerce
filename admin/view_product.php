<?php
require_once __DIR__ . '/../components/connect.php';
require_seller();
$isSystemAdmin = is_system_admin($conn);

if (is_post() && isset($_POST['toggle_status'])) {
    $id = clean_text((string) ($_POST['product_id'] ?? ''), 20);
    $find = $conn->prepare('SELECT seller_id FROM products WHERE id = ? LIMIT 1');
    $find->execute([$id]);
    $owner = $find->fetchColumn();
    if (!$owner || !can_manage_seller_record($conn, (string) $owner)) {
        flash('error', 'Product not found or access denied.');
    } else {
        $stmt = $conn->prepare("UPDATE products SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = ?");
        $stmt->execute([$id]);
        flash('success', 'Product status updated.');
    }
    redirect('admin/view_product.php');
}

if (is_post() && isset($_POST['delete_product'])) {
    $id = clean_text((string) ($_POST['product_id'] ?? ''), 20);
    try {
        $conn->beginTransaction();
        $own = $conn->prepare('SELECT seller_id FROM products WHERE id = ? FOR UPDATE');
        $own->execute([$id]);
        $owner = $own->fetchColumn();
        if (!$owner || !can_manage_seller_record($conn, (string) $owner)) {
            throw new RuntimeException('Product not found or access denied.');
        }
        $conn->prepare('DELETE FROM cart WHERE product_id = ?')->execute([$id]);
        $conn->prepare('DELETE FROM wishlist WHERE product_id = ?')->execute([$id]);
        $conn->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
        $conn->commit();
        flash('success', 'Product deleted. Existing order history was preserved.');
    } catch (Throwable $exception) {
        if ($conn->inTransaction()) $conn->rollBack();
        flash('error', friendly_exception($exception, 'The product could not be deleted. Please try again.'));
    }
    redirect('admin/view_product.php');
}

$q = clean_text((string) ($_GET['q'] ?? ''), 100);
$status = clean_text((string) ($_GET['status'] ?? ''), 20);
$where = [];
$params = [];
if (!$isSystemAdmin) {
    $where[] = 'p.seller_id = ?';
    $params[] = $seller_id;
}
if ($q !== '') {
    $where[] = '(p.name LIKE ? OR p.category LIKE ? OR s.name LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if (in_array($status, ['active', 'inactive'], true)) {
    $where[] = 'p.status = ?';
    $params[] = $status;
}
$sql = 'SELECT p.*, s.name AS seller_name, s.email AS seller_email FROM products p LEFT JOIN sellers s ON s.id = p.seller_id';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY p.created_at DESC, p.name ASC';
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Products | Admin</title><link rel="stylesheet" href="<?= e(app_url('css/admin_style.css')) ?>"></head><body>
<?php include __DIR__ . '/../components/admin_header.php'; ?>
<main class="admin-main"><div class="admin-container"><div class="admin-page-heading"><div><h1>Products</h1><p><?= $isSystemAdmin ? 'View and manage products from every registered seller.' : 'View, edit, activate, deactivate, or delete your products.' ?></p></div><a class="button" href="<?= e(app_url('admin/add_product.php')) ?>">Add product</a></div>
<section class="panel"><form method="get" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px"><input style="flex:1;min-width:220px;padding:10px;border:1px solid var(--line);border-radius:10px" name="q" value="<?= e($q) ?>" placeholder="Search products<?= $isSystemAdmin ? ' or sellers' : '' ?>"><select style="padding:10px;border:1px solid var(--line);border-radius:10px" name="status"><option value="">All statuses</option><option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option></select><button class="button" type="submit">Filter</button></form>
<?php if ($products): ?>
<div class="table-wrap">
  <table class="data-table">
    <thead>
      <tr>
        <th>Product</th>
        <?php if ($isSystemAdmin): ?><th>Seller</th><?php endif; ?>
        <th>Category</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $product): ?>
      <tr>
        <td>
          <div class="table-product">
            <img src="<?= e(uploaded_image_url($product['thumb_one'])) ?>" alt="Product">
            <div class="table-product-details">
              <strong><?= e($product['name']) ?></strong>
              <small class="text-muted">#<?= e($product['id']) ?></small>
            </div>
          </div>
        </td>
        <?php if ($isSystemAdmin): ?>
        <td>
          <div class="table-seller">
            <strong><?= e($product['seller_name'] ?? 'Unknown') ?></strong>
            <small class="text-muted"><?= e($product['seller_email'] ?? '') ?></small>
          </div>
        </td>
        <?php endif; ?>
        <td><?= e($product['category']) ?></td>
        <td><?= e(money($product['price'])) ?></td>
        <td><?= (int) $product['stock'] ?></td>
        <td><span class="badge <?= e($product['status']) ?>"><?= e($product['status']) ?></span></td>
        <td>
          <div class="actions">
            <a class="button secondary small" href="<?= e(app_url('admin/read_product.php?id=' . urlencode($product['id']))) ?>">View</a>
            <a class="button small" href="<?= e(app_url('admin/edit_product.php?id=' . urlencode($product['id']))) ?>">Edit</a>
            <form method="post">
              <?= csrf_field() ?>
              <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">
              <button class="button secondary small" type="submit" name="toggle_status"><?= $product['status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
            </form>
            <form method="post" onsubmit="return confirm('Delete this product? This cannot be undone.')">
              <?= csrf_field() ?>
              <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">
              <button class="button danger small" type="submit" name="delete_product">Delete</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="empty-state">No products match the selected filters.</div>
<?php endif; ?>
</section>
</div>
</main>
<?php include __DIR__ . '/../components/admin_footer.php'; include __DIR__ . '/../components/alert.php'; ?>
</body></html>
