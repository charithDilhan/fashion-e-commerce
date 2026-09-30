<?php
require_once __DIR__ . '/components/connect.php';
require_user();
require_once __DIR__ . '/components/add_cart.php';
if (is_post() && isset($_POST['remove_wishlist'])) {
    $id = clean_text((string) ($_POST['wishlist_id'] ?? ''), 20);
    $stmt = $conn->prepare('DELETE FROM wishlist WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $user_id]);
    flash('success', 'Item removed from wishlist.');
    redirect('wishlist.php');
}
if (is_post() && isset($_POST['clear_wishlist'])) {
    $stmt = $conn->prepare('DELETE FROM wishlist WHERE user_id = ?');
    $stmt->execute([$user_id]);
    flash('success', 'Wishlist cleared.');
    redirect('wishlist.php');
}
$stmt = $conn->prepare("SELECT w.id AS wishlist_id, p.* FROM wishlist w JOIN products p ON p.id = w.product_id WHERE w.user_id = ? AND p.status = 'active' ORDER BY w.created_at DESC");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Wishlist | Fashion Store</title><link rel="stylesheet" href="<?= e(app_url('css/user_style.css')) ?>?v=<?= filemtime(app_root_path().'/css/user_style.css') ?>"></head><body>
<?php include __DIR__ . '/components/user_header.php'; ?>
<section class="page-hero"><div class="container"><h1>Wishlist</h1><p>Products you saved for later.</p></div></section>
<section class="section"><div class="container">
<?php if ($items): ?><div class="product-grid"><?php foreach ($items as $product): ?><article class="product-card"><a class="product-image" href="<?= e(app_url('view_page.php?id=' . urlencode($product['id']))) ?>"><img src="<?= e(uploaded_image_url($product['thumb_one'])) ?>" alt="<?= e($product['name']) ?>"></a><div class="product-info"><div class="product-meta"><span><?= e($product['category']) ?></span><span class="stock <?= (int)$product['stock'] < 1 ? 'out' : '' ?>"><?= (int)$product['stock'] > 0 ? (int)$product['stock'].' in stock' : 'Out of stock' ?></span></div><h3><?= e($product['name']) ?></h3><div class="price"><?= e(money($product['price'])) ?></div><div class="card-actions"><form method="post"><?= csrf_field() ?><input type="hidden" name="wishlist_id" value="<?= e($product['wishlist_id']) ?>"><button type="submit" name="remove_wishlist">Remove</button></form><form method="post"><?= csrf_field() ?><input type="hidden" name="product_id" value="<?= e($product['id']) ?>"><input type="hidden" name="qty" value="1"><input type="hidden" name="return_to" value="wishlist.php"><button class="primary" type="submit" name="add_to_cart" <?= (int)$product['stock'] < 1 ? 'disabled' : '' ?>>Add to cart</button></form></div></div></article><?php endforeach; ?></div><form method="post" style="margin-top:24px" onsubmit="return confirm('Clear your entire wishlist?')"><?= csrf_field() ?><button class="button danger" type="submit" name="clear_wishlist">Clear wishlist</button></form><?php else: ?><div class="empty-state"><h3>Your wishlist is empty</h3><p>Save products you like and return to them later.</p><a class="button" href="<?= e(app_url('shop.php')) ?>">Explore products</a></div><?php endif; ?>
</div></section>
<?php include __DIR__ . '/components/user_footer.php'; include __DIR__ . '/components/alert.php'; ?>
</body></html>

