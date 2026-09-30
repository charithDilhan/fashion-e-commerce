<?php
require_once __DIR__ . '/components/connect.php';
require_once __DIR__ . '/components/add_cart.php';
require_once __DIR__ . '/components/add_wishlist.php';

$q = clean_text((string) ($_GET['q'] ?? ''), 100);
$category = clean_text((string) ($_GET['category'] ?? ''), 100);
$sort = (string) ($_GET['sort'] ?? 'newest');
$params = [];
$where = ["p.status = 'active'"];
if ($q !== '') {
    $where[] = '(p.name LIKE ? OR p.category LIKE ? OR p.product_detail LIKE ?)';
    $term = '%' . $q . '%';
    array_push($params, $term, $term, $term);
}
if ($category !== '') {
    if ($category === "Men's Clothing" || $category === "MEN") {
        $where[] = "(p.category = 'Men\'s Clothing' OR p.category = 'MEN')";
    } elseif ($category === "Women's Clothing" || $category === "WOMEN") {
        $where[] = "(p.category = 'Women\'s Clothing' OR p.category = 'WOMEN')";
    } elseif ($category === "Kids' Clothing" || $category === "KIDS") {
        $where[] = "(p.category = 'Kids\' Clothing' OR p.category = 'KIDS')";
    } elseif ($category === "Baby & Toddler Wear") {
        $where[] = "(p.category = 'Baby & Toddler Wear' OR (p.category = 'Kids\' Clothing' AND (p.name LIKE '%baby%' OR p.product_detail LIKE '%baby%' OR p.name LIKE '%toddler%' OR p.product_detail LIKE '%toddler%')))";
    } elseif ($category === "Men's Footwear") {
        $where[] = "(p.category = 'Men\'s Footwear' OR (p.category = 'Footwear' AND (p.name LIKE '%men%' OR p.product_detail LIKE '%men%')))";
    } elseif ($category === "Women's Footwear") {
        $where[] = "(p.category = 'Women\'s Footwear' OR (p.category = 'Footwear' AND (p.name LIKE '%women%' OR p.product_detail LIKE '%women%')))";
    } elseif ($category === "Kids' Footwear") {
        $where[] = "(p.category = 'Kids\' Footwear' OR (p.category = 'Footwear' AND (p.name LIKE '%kid%' OR p.product_detail LIKE '%kid%' OR p.name LIKE '%child%' OR p.product_detail LIKE '%child%')))";
    } elseif ($category === "Men's Accessories") {
        $where[] = "(p.category = 'Men\'s Accessories' OR (p.category = 'Accessories' AND (p.name LIKE '%men%' OR p.product_detail LIKE '%men%')))";
    } elseif ($category === "Women's Accessories") {
        $where[] = "(p.category = 'Women\'s Accessories' OR (p.category = 'Accessories' AND (p.name LIKE '%women%' OR p.product_detail LIKE '%women%')))";
    } elseif ($category === "Handbags & Purses") {
        $where[] = "(p.category = 'Handbags & Purses' OR (p.category = 'Accessories' AND (p.name LIKE '%bag%' OR p.product_detail LIKE '%bag%' OR p.name LIKE '%purse%' OR p.product_detail LIKE '%purse%')))";
    } elseif ($category === "School Bags & Toys") {
        $where[] = "(p.category = 'School Bags & Toys' OR (p.category = 'Accessories' AND (p.name LIKE '%toy%' OR p.product_detail LIKE '%toy%' OR p.name LIKE '%school%' OR p.product_detail LIKE '%school%')))";
    } else {
        $where[] = 'p.category = ?';
        $params[] = $category;
    }
}
$orderBy = match ($sort) {
    'price_low' => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'name' => 'p.name ASC',
    default => 'p.created_at DESC, p.name ASC',
};
$stmt = $conn->prepare('SELECT p.* FROM products p WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $orderBy);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Shop | Fashion Store</title><link rel="stylesheet" href="<?= e(app_url('css/user_style.css')) ?>?v=<?= filemtime(app_root_path().'/css/user_style.css') ?>"></head><body>
<?php include __DIR__ . '/components/user_header.php'; ?>
<section class="page-hero" style="--hero-image:url('https://images.pexels.com/photos/7679720/pexels-photo-7679720.jpeg?auto=compress&cs=tinysrgb&w=1600');"><div class="container"><div class="hero-copy"><span class="eyebrow">Fashion collection</span><h1>Shop the latest looks</h1><p>Browse <?= count($products) ?> available product<?= count($products) === 1 ? '' : 's' ?> with a premium storefront experience and easy filtering.</p></div></div></section>
<section class="section"><div class="container">
<form class="filters" method="get">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search by product, category, or description">
    <select name="category"><option value="">All categories</option><?php foreach (product_categories() as $option): ?><option value="<?= e($option) ?>" <?= $category === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select>
    <select name="sort"><option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option><option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: low to high</option><option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: high to low</option><option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name</option></select>
    <button class="button" type="submit">Apply</button>
</form>
<?php if ($products): ?><div class="product-grid"><?php foreach ($products as $product): include __DIR__ . '/components/product_card.php'; endforeach; ?></div><?php else: ?><div class="empty-state"><h3>No matching products</h3><p>Try a different search or category.</p><a class="button" href="<?= e(app_url('shop.php')) ?>">Clear filters</a></div><?php endif; ?>
</div></section>
<?php include __DIR__ . '/components/user_footer.php'; include __DIR__ . '/components/alert.php'; ?>
</body></html>

