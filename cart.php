<?php
require_once __DIR__ . '/components/connect.php';
require_user();
if (is_post() && isset($_POST['update_cart'])) {
    $id = clean_text((string) ($_POST['cart_id'] ?? ''), 20);
    $qty = max(1, min(99, (int) ($_POST['qty'] ?? 1)));
    $stmt = $conn->prepare('SELECT c.*, p.stock FROM cart c JOIN products p ON p.id = c.product_id WHERE c.id = ? AND c.user_id = ? LIMIT 1');
    $stmt->execute([$id, $user_id]);
    $item = $stmt->fetch();
    if ($item) {
        $qty = min($qty, (int) $item['stock']);
        if ($qty < 1) {
            $delete = $conn->prepare('DELETE FROM cart WHERE id = ? AND user_id = ?');
            $delete->execute([$id, $user_id]);
            flash('warning', 'Out-of-stock product removed from your cart.');
        } else {
            $update = $conn->prepare('UPDATE cart SET qty = ? WHERE id = ? AND user_id = ?');
            $update->execute([$qty, $id, $user_id]);
            flash('success', 'Cart updated.');
        }
    }
    redirect('cart.php');
}
if (is_post() && isset($_POST['remove_cart'])) {
    $id = clean_text((string) ($_POST['cart_id'] ?? ''), 20);
    $stmt = $conn->prepare('DELETE FROM cart WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $user_id]);
    flash('success', 'Item removed from cart.');
    redirect('cart.php');
}
if (is_post() && isset($_POST['clear_cart'])) {
    $stmt = $conn->prepare('DELETE FROM cart WHERE user_id = ?');
    $stmt->execute([$user_id]);
    flash('success', 'Cart cleared.');
    redirect('cart.php');
}
$stmt = $conn->prepare("SELECT c.id AS cart_id, c.qty, c.option_size, p.* FROM cart c JOIN products p ON p.id = c.product_id WHERE c.user_id = ? AND p.status = 'active' ORDER BY c.created_at DESC");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();
$total = 0;
foreach ($items as $item) $total += (float)$item['price'] * (int)$item['qty'];
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Cart | Fashion Store</title><link rel="stylesheet" href="<?= e(app_url('css/user_style.css')) ?>?v=<?= filemtime(app_root_path().'/css/user_style.css') ?>"></head><body>
<?php include __DIR__ . '/components/user_header.php'; ?>
<section class="page-hero"><div class="container"><h1>Shopping cart</h1><p>Review quantities before checkout.</p></div></section>
<section class="section"><div class="container">
<?php if ($items): ?><div class="two-column"><div class="table-wrap"><table class="data-table"><thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th></th></tr></thead><tbody><?php foreach ($items as $item): ?><tr><td><div class="table-product"><img src="<?= e(uploaded_image_url($item['thumb_one'])) ?>" alt=""><div><strong><?= e($item['name']) ?></strong><div class="muted"><?= e($item['category']) ?><?= $item['option_size'] !== '' ? ' • Size: ' . e($item['option_size']) : '' ?></div></div></div></td><td><?= e(money($item['price'])) ?></td><td><form method="post" style="display:flex;gap:6px;align-items:center"><?= csrf_field() ?><input type="hidden" name="cart_id" value="<?= e($item['cart_id']) ?>"><input style="width:75px;padding:8px;border:1px solid var(--line);border-radius:8px" type="number" name="qty" min="1" max="<?= max(1,(int)$item['stock']) ?>" value="<?= (int)$item['qty'] ?>"><button class="button secondary small" type="submit" name="update_cart">Update</button></form></td><td><?= e(money((float)$item['price']*(int)$item['qty'])) ?></td><td><form method="post" onsubmit="return confirm('Remove this item?')"><?= csrf_field() ?><input type="hidden" name="cart_id" value="<?= e($item['cart_id']) ?>"><button class="button danger small" type="submit" name="remove_cart">Remove</button></form></td></tr><?php endforeach; ?></tbody></table></div><aside class="summary-card"><h3>Order summary</h3><div class="summary-row"><span>Items</span><strong><?= cart_count($conn,$user_id) ?></strong></div><div class="summary-row"><span>Delivery</span><strong>Calculated at checkout</strong></div><div class="summary-row total"><span>Total</span><span><?= e(money($total)) ?></span></div><a class="button" style="width:100%;margin-top:18px" href="<?= e(app_url('checkout.php')) ?>">Proceed to checkout</a><form method="post" onsubmit="return confirm('Clear your cart?')" style="margin-top:10px"><?= csrf_field() ?><button class="button secondary" style="width:100%" type="submit" name="clear_cart">Clear cart</button></form></aside></div><?php else: ?><div class="empty-state"><h3>Your cart is empty</h3><p>Add products before proceeding to checkout.</p><a class="button" href="<?= e(app_url('shop.php')) ?>">Start shopping</a></div><?php endif; ?>
</div></section>
<?php include __DIR__ . '/components/user_footer.php'; include __DIR__ . '/components/alert.php'; ?>
</body></html>

