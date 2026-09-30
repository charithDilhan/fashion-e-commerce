<?php
$user = current_user($conn);
$cartItemsCount = cart_count($conn, $user_id);
$wishlistItemsCount = wishlist_count($conn, $user_id);
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$categoryFilter = clean_text((string) ($_GET['category'] ?? ''), 100);
$qFilter = strtolower(clean_text((string) ($_GET['q'] ?? ''), 100));

$isMenActive = $currentPage === 'shop.php' && (
    $categoryFilter === "Men's Clothing" || $categoryFilter === "Men's Footwear" || $categoryFilter === "Men's Accessories" || $categoryFilter === "MEN" ||
    (in_array($categoryFilter, ["Footwear", "Men's Footwear", "Accessories", "Men's Accessories"], true) && str_contains($qFilter, 'men'))
);
$isWomenActive = $currentPage === 'shop.php' && (
    in_array($categoryFilter, ["Women's Clothing", "Women's Footwear", "Women's Accessories", "Handbags & Purses", "Western Dresses", "Ethnic Wear", "WOMEN"], true) ||
    (in_array($categoryFilter, ["Footwear", "Women's Footwear", "Accessories", "Women's Accessories"], true) && str_contains($qFilter, 'women'))
);
$isKidsActive = $currentPage === 'shop.php' && (
    in_array($categoryFilter, ["Kids' Clothing", "Baby & Toddler Wear", "Kids' Footwear", "School Bags & Toys", "KIDS"], true) ||
    (in_array($categoryFilter, ["Footwear", "Accessories"], true) && str_contains($qFilter, 'kid'))
);
?>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= e(app_url('home.php')) ?>" aria-label="Fashion home">
            <img src="<?= e(app_url('assets/logo.svg')) ?>" alt="Fashion Store logo">
        </a>
        <button class="menu-toggle" type="button" aria-label="Open menu" data-menu-toggle><svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
        <nav class="main-nav" data-main-nav>
            <a class="<?= $currentPage === 'home.php' ? 'active' : '' ?>" href="<?= e(app_url('home.php')) ?>">Home</a>
            
            <div class="nav-item dropdown">
                <a class="dropdown-toggle <?= $isMenActive ? 'active' : '' ?>" href="<?= e(app_url('shop.php?category=Men%27s+Clothing')) ?>">Men</a>
                <div class="dropdown-menu">
                    <a href="<?= e(app_url('shop.php?category=Men%27s+Clothing')) ?>">All Men's Clothing</a>
                    <a href="<?= e(app_url('shop.php?category=Men%27s+Clothing&q=shirt')) ?>">Shirts & T-Shirts</a>
                    <a href="<?= e(app_url('shop.php?category=Men%27s+Clothing&q=pant')) ?>">Trousers & Pants</a>
                    <a href="<?= e(app_url('shop.php?category=Men%27s+Clothing&q=blazer')) ?>">Suits & Blazers</a>
                    <a href="<?= e(app_url('shop.php?category=Men%27s+Footwear')) ?>">Men's Footwear</a>
                    <a href="<?= e(app_url('shop.php?category=Men%27s+Accessories')) ?>">Watches & Accessories</a>
                </div>
            </div>
            
            <div class="nav-item dropdown">
                <a class="dropdown-toggle <?= $isWomenActive ? 'active' : '' ?>" href="<?= e(app_url('shop.php?category=Women%27s+Clothing')) ?>">Women</a>
                <div class="dropdown-menu">
                    <a href="<?= e(app_url('shop.php?category=Women%27s+Clothing')) ?>">All Women's Wear</a>
                    <a href="<?= e(app_url('shop.php?category=Western+Dresses')) ?>">Western Dresses</a>
                    <a href="<?= e(app_url('shop.php?category=Ethnic+Wear')) ?>">Sarees & Ethnic Wear</a>
                    <a href="<?= e(app_url('shop.php?category=Handbags+%26+Purses')) ?>">Handbags & Purses</a>
                    <a href="<?= e(app_url('shop.php?category=Women%27s+Footwear')) ?>">Women's Footwear</a>
                    <a href="<?= e(app_url('shop.php?category=Women%27s+Accessories')) ?>">Jewellery & Accessories</a>
                </div>
            </div>
            
            <div class="nav-item dropdown">
                <a class="dropdown-toggle <?= $isKidsActive ? 'active' : '' ?>" href="<?= e(app_url('shop.php?category=Kids%27+Clothing')) ?>">Kids</a>
                <div class="dropdown-menu">
                    <a href="<?= e(app_url('shop.php?category=Kids%27+Clothing')) ?>">All Kids' Clothing</a>
                    <a href="<?= e(app_url('shop.php?category=Kids%27+Clothing&q=boy')) ?>">Boys' Collection</a>
                    <a href="<?= e(app_url('shop.php?category=Kids%27+Clothing&q=girl')) ?>">Girls' Dresses & Frocks</a>
                    <a href="<?= e(app_url('shop.php?category=Baby+%26+Toddler+Wear')) ?>">Baby & Toddler Wear</a>
                    <a href="<?= e(app_url('shop.php?category=Kids%27+Footwear')) ?>">Kids' Footwear</a>
                    <a href="<?= e(app_url('shop.php?category=School+Bags+%26+Toys')) ?>">School Bags & Toys</a>
                </div>
            </div>

            
            <a class="<?= $currentPage === 'about.php' ? 'active' : '' ?>" href="<?= e(app_url('about.php')) ?>">About</a>
            <a class="<?= $currentPage === 'contact.php' ? 'active' : '' ?>" href="<?= e(app_url('contact.php')) ?>">Contact</a>
            <?php if ($user): ?>
                <a class="<?= $currentPage === 'order.php' ? 'active' : '' ?>" href="<?= e(app_url('order.php')) ?>">Orders</a>
            <?php endif; ?>
        </nav>
        <form class="header-search" action="<?= e(app_url('shop.php')) ?>" method="get">
            <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search products" aria-label="Search products">
            <button type="submit" aria-label="Search"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="2"/><path d="m16 16 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
        </form>
        <div class="header-actions">
            <a class="icon-link" href="<?= e(app_url('wishlist.php')) ?>" aria-label="Wishlist"><svg viewBox="0 0 24 24" width="19" height="19" aria-hidden="true"><path d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.5a5.5 5.5 0 0 0 0-7.8Z" fill="none" stroke="currentColor" stroke-width="1.8"/></svg><span><?= $wishlistItemsCount ?></span></a>
            <a class="icon-link" href="<?= e(app_url('cart.php')) ?>" aria-label="Cart"><svg viewBox="0 0 24 24" width="19" height="19" aria-hidden="true"><path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L20 8H6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="10" cy="20" r="1.2"/><circle cx="18" cy="20" r="1.2"/></svg><span><?= $cartItemsCount ?></span></a>
            <button class="avatar-button" type="button" data-profile-toggle aria-label="Account">
                <img src="<?= e(uploaded_image_url($user['image'] ?? '', 'avatar')) ?>" alt="Account">
            </button>
        </div>
        <div class="profile-menu" data-profile-menu>
            <?php if ($user): ?>
                <div class="profile-summary">
                    <img src="<?= e(uploaded_image_url($user['image'] ?? '', 'avatar')) ?>" alt="<?= e($user['name']) ?>">
                    <div><strong><?= e($user['name']) ?></strong><small><?= e($user['email']) ?></small></div>
                </div>
                <a href="<?= e(app_url('profile.php')) ?>">My profile</a>
                <a href="<?= e(app_url('order.php')) ?>">My orders</a>
                <form action="<?= e(app_url('components/user_logout.php')) ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit">Logout</button>
                </form>
            <?php else: ?>
                <p class="muted">Login to use cart, wishlist, and orders.</p>
                <a class="button small" href="<?= e(app_url('login.php')) ?>">Login</a>
                <a class="button secondary small" href="<?= e(app_url('register.php')) ?>">Create account</a>
            <?php endif; ?>
        </div>
    </div>
</header>
