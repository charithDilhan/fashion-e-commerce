<article class="product-card">
    <a class="product-image" href="<?= e(app_url('view_page.php?id=' . urlencode($product['id']))) ?>">
        <img src="<?= e(uploaded_image_url($product['thumb_one'] ?? '')) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
    </a>
    <div class="product-info">
        <h3 class="product-title">
            <a href="<?= e(app_url('view_page.php?id=' . urlencode($product['id']))) ?>"><?= e($product['name']) ?></a>
        </h3>
        <?php 
            $cleanDetail = !empty($product['product_detail']) ? clean_text(clean_product_detail($product['product_detail']), 75) : '';
            if ($cleanDetail !== ''): 
        ?>
            <p class="product-desc"><?= e($cleanDetail) ?></p>
        <?php endif; ?>
        <div class="price"><?= e(money($product['price'])) ?></div>
        <div class="product-meta">
            <span><?= e($product['category'] ?: 'Fashion') ?></span>
            <span class="stock <?= (int) $product['stock'] < 1 ? 'out' : '' ?>"><?= (int) $product['stock'] > 0 ? (int) $product['stock'] . ' in stock' : 'Out of stock' ?></span>
        </div>
        <div class="card-actions">
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">
                <input type="hidden" name="qty" value="1">
                <input type="hidden" name="return_to" value="<?= e(basename($_SERVER['PHP_SELF']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')) ?>">
                <button type="submit" name="add_to_wishlist" aria-label="Add to wishlist">♡ Save</button>
            </form>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">
                <input type="hidden" name="qty" value="1">
                <input type="hidden" name="return_to" value="<?= e(basename($_SERVER['PHP_SELF']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')) ?>">
                <button class="primary" type="submit" name="add_to_cart" <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?>>Add to cart</button>
            </form>
        </div>
    </div>
</article>
