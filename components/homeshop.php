<?php
$homeProducts = $conn->query("SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC, name ASC LIMIT 8")->fetchAll();
?>
<div class="product-grid">
    <?php foreach ($homeProducts as $product): ?>
        <?php include __DIR__ . '/product_card.php'; ?>
    <?php endforeach; ?>
</div>
<?php if (!$homeProducts): ?>
    <div class="empty-state"><h3>No active products yet</h3><p>Products added by the admin will appear here.</p></div>
<?php endif; ?>
