<?php
require_once __DIR__ . '/../components/connect.php';
require_seller();
$id = clean_text((string) ($_GET['id'] ?? $_POST['product_id'] ?? ''), 20);
$stmt = $conn->prepare('SELECT p.*, s.name AS seller_name FROM products p LEFT JOIN sellers s ON s.id = p.seller_id WHERE p.id = ? LIMIT 1');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product || !can_manage_seller_record($conn, (string) $product['seller_id'])) {
    flash('error', 'Product not found or access denied.');
    redirect('admin/view_product.php');
}
$errors = [];
if (is_post() && isset($_POST['update_product'])) {
    $name = clean_text((string) ($_POST['name'] ?? ''), 80);
    $price = (float) ($_POST['price'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $category = clean_text((string) ($_POST['category'] ?? ''), 100);
    $detail = trim((string) ($_POST['product_detail'] ?? ''));
    $status = in_array($_POST['status'] ?? 'active', ['active', 'inactive'], true) ? $_POST['status'] : 'active';
    if (strlen($name) < 2) $errors[] = 'Product name is required.';
    if ($price <= 0) $errors[] = 'Price must be greater than zero.';
    if ($stock < 0) $errors[] = 'Stock cannot be negative.';
    if (!in_array($category, product_categories(), true)) $errors[] = 'Select a valid category.';
    if (strlen($detail) < 5) $errors[] = 'Add a clear product description.';
    if (strlen($detail) > 5000) $errors[] = 'Product description is too long.';
    if (!$errors) {
        try {
            $one = upload_image('thumb_one', $product['thumb_one']);
            $two = upload_image('thumb_two', $product['thumb_two']);
            $three = upload_image('thumb_three', $product['thumb_three']);
            $update = $conn->prepare('UPDATE products SET name = ?, price = ?, category = ?, thumb_one = ?, thumb_two = ?, thumb_three = ?, stock = ?, product_detail = ?, status = ? WHERE id = ?');
            $update->execute([$name, $price, $category, $one, $two, $three, $stock, $detail, $status, $id]);
            flash('success', 'Product updated successfully.');
            redirect('admin/view_product.php');
        } catch (Throwable $exception) {
            $errors[] = friendly_exception($exception, 'The product could not be updated. Please try again.');
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Edit Product | Admin</title><link rel="stylesheet" href="<?= e(app_url('css/admin_style.css')) ?>"></head><body>
<?php include __DIR__ . '/../components/admin_header.php'; ?>
<main class="admin-main"><div class="admin-container"><div class="admin-page-heading"><div><h1>Edit product</h1><p>All current values and images are preserved unless changed.<?php if (is_system_admin($conn)): ?> Seller: <?= e($product['seller_name'] ?? 'Unknown') ?>.<?php endif; ?></p></div></div><form class="panel" method="post" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="product_id" value="<?= e($id) ?>"><?php foreach ($errors as $item): ?><p class="badge canceled"><?= e($item) ?></p><?php endforeach; ?><div class="form-grid"><div class="field"><label>Product name</label><input name="name" required value="<?= e($_POST['name'] ?? $product['name']) ?>"></div><div class="field"><label>Category</label><select name="category" required><?php foreach (product_categories() as $cat): ?><option value="<?= e($cat) ?>" <?= ($_POST['category'] ?? $product['category']) === $cat ? 'selected' : '' ?>><?= e($cat) ?></option><?php endforeach; ?></select></div><div class="field"><label>Price (LKR)</label><input type="number" step="0.01" min="0.01" name="price" required value="<?= e($_POST['price'] ?? $product['price']) ?>"></div><div class="field"><label>Stock</label><input type="number" min="0" name="stock" required value="<?= e($_POST['stock'] ?? $product['stock']) ?>"></div><div class="field"><label>Status</label><select name="status"><option value="active" <?= ($_POST['status'] ?? $product['status']) === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= ($_POST['status'] ?? $product['status']) === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></div><div class="field full"><label>Description <small style="color:var(--muted); font-weight:normal; display:inline-block; margin-left:6px;">(Tip: Add sizes like <code>Size: [04] [05] [06]</code> or <code>Size: S, M, L</code> for interactive size buttons)</small></label><textarea name="product_detail" required><?= e($_POST['product_detail'] ?? $product['product_detail']) ?></textarea></div><?php foreach (['thumb_one' => 'Main image', 'thumb_two' => 'Second image', 'thumb_three' => 'Third image'] as $field => $label): ?><div class="field"><label><?= e($label) ?> (optional replacement)</label><img class="image-preview" src="<?= e(uploaded_image_url($product[$field])) ?>" alt=""><input type="file" name="<?= e($field) ?>" accept="image/jpeg,image/png,image/webp,image/avif"></div><?php endforeach; ?></div><div style="margin-top:20px;display:flex;gap:10px"><button class="button" type="submit" name="update_product">Save changes</button><a class="button secondary" href="<?= e(app_url('admin/view_product.php')) ?>">Cancel</a></div></form></div></main>
<?php include __DIR__ . '/../components/admin_footer.php'; include __DIR__ . '/../components/alert.php'; ?>
</body></html>
