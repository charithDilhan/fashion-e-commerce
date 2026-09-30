<?php
require_once __DIR__ . '/components/connect.php';
require_once __DIR__ . '/components/add_cart.php';
require_once __DIR__ . '/components/add_wishlist.php';
$id = clean_text((string) ($_GET['id'] ?? $_POST['product_id'] ?? ''), 20);
$product = fetch_product($conn, $id, true);
if (!$product) {
    http_response_code(404);
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= $product ? e($product['name']) : 'Product not found' ?> | Fashion Store</title><link rel="stylesheet" href="<?= e(app_url('css/user_style.css')) ?>?v=<?= filemtime(app_root_path().'/css/user_style.css') ?>"></head><body>
<?php include __DIR__ . '/components/user_header.php'; ?>
<section class="page-hero page-hero-sm" style="--hero-image:url('https://images.pexels.com/photos/1036841/pexels-photo-1036841.jpeg?auto=compress&cs=tinysrgb&w=1600');"><div class="container"><div class="hero-copy"><span class="eyebrow">Product details</span><h1><?= $product ? e($product['name']) : 'Product not found' ?></h1><p><?= $product ? e($product['category'] ?: 'Fashion item') : 'This item may have been removed or deactivated.' ?></p></div></div></section>
<section class="section"><div class="container">
<?php if (!$product): ?>
<div class="empty-state"><h3>Product not found</h3><p>This product may have been removed or deactivated.</p><a class="button" href="<?= e(app_url('shop.php')) ?>">Back to shop</a></div>
<?php else: 
    $sizes = parse_product_sizes((string)$product['product_detail']);
    $cleanedDetail = clean_product_detail((string)$product['product_detail']);

    // Collect all valid unique thumbnail images for gallery
    $galleryThumbs = [];
    if (!empty($product['thumb_one'])) {
        $galleryThumbs[] = uploaded_image_url($product['thumb_one']);
    }
    if (!empty($product['thumb_two']) && $product['thumb_two'] !== $product['thumb_one']) {
        $galleryThumbs[] = uploaded_image_url($product['thumb_two']);
    }
    if (!empty($product['thumb_three']) && $product['thumb_three'] !== $product['thumb_one'] && $product['thumb_three'] !== $product['thumb_two']) {
        $galleryThumbs[] = uploaded_image_url($product['thumb_three']);
    }
    if (empty($galleryThumbs)) {
        $galleryThumbs[] = uploaded_image_url(null);
    }
    $mainImgUrl = $galleryThumbs[0];
?>
<div class="detail-grid">
    <div>
        <div class="gallery-main">
            <img id="mainProductImg" src="<?= e($mainImgUrl) ?>" alt="<?= e($product['name']) ?>">
        </div>
        <div class="thumbnail-row">
            <?php foreach ($galleryThumbs as $idx => $thumbUrl): ?>
                <img src="<?= e($thumbUrl) ?>" alt="<?= e($product['name']) ?> thumbnail <?= $idx + 1 ?>" class="thumb-img <?= $idx === 0 ? 'active' : '' ?>" onclick="switchProductImage(this, '<?= e($thumbUrl) ?>')">
            <?php endforeach; ?>
        </div>
    </div>
    <div class="product-detail product-detail-card"><span class="eyebrow"><?= e($product['category'] ?: 'Fashion') ?></span><h1><?= e($product['name']) ?></h1><div class="detail-price"><?= e(money($product['price'])) ?></div><p class="muted">Sold by <?= e($product['seller_name'] ?: 'Fashion Store') ?></p><p><?= nl2br(e($cleanedDetail !== '' ? $cleanedDetail : $product['product_detail'])) ?></p><p class="stock <?= (int)$product['stock'] < 1 ? 'out' : '' ?>"><?= (int)$product['stock'] > 0 ? (int)$product['stock'] . ' item(s) available' : 'Currently out of stock' ?></p>
        <form method="post">
            <?php if ($sizes): ?>
            <div class="size-selection-box" style="margin: 16px 0;">
                <label style="display:block; font-weight:600; margin-bottom:8px; font-size: 0.95rem;">Select Size:</label>
                <div class="size-options-row" style="display:flex; flex-wrap:wrap; gap:8px;">
                    <?php foreach ($sizes as $idx => $sz): ?>
                        <button type="button" class="size-btn <?= $idx === 0 ? 'active' : '' ?>" onclick="selectProductSize(this, '<?= e($sz) ?>')">
                            <?= e($sz) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="option_size" id="selected_product_size" value="<?= e($sizes[0]) ?>">
            </div>
            <?php endif; ?>
            <div class="quantity-control"><label for="qty">Quantity</label><input id="qty" type="number" name="qty" min="1" max="<?= max(1,(int)$product['stock']) ?>" value="1"></div><?= csrf_field() ?><input type="hidden" name="product_id" value="<?= e($product['id']) ?>"><input type="hidden" name="return_to" value="view_page.php?id=<?= e(urlencode($product['id'])) ?>"><div class="button-row"><button class="button" type="submit" name="add_to_cart" <?= (int)$product['stock'] < 1 ? 'disabled' : '' ?>>Add to cart</button><button class="button secondary" type="submit" name="add_to_wishlist">Save to wishlist</button></div>
        </form>
    </div>
</div>
<script>
function switchProductImage(thumbBtn, newSrc) {
    var mainImg = document.getElementById('mainProductImg');
    if (mainImg) {
        mainImg.src = newSrc;
    }
    var thumbs = document.querySelectorAll('.thumbnail-row .thumb-img');
    thumbs.forEach(function(t) { t.classList.remove('active'); });
    thumbBtn.classList.add('active');
}

function selectProductSize(btn, size) {
    var container = btn.closest('.size-selection-box');
    var buttons = container.querySelectorAll('.size-btn');
    buttons.forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
    document.getElementById('selected_product_size').value = size;
}
</script>
<?php endif; ?>
</div></section>
<?php include __DIR__ . '/components/user_footer.php'; include __DIR__ . '/components/alert.php'; ?>
</body></html>

