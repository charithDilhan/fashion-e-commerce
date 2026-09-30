<?php
require_once __DIR__ . '/components/connect.php';
require_once __DIR__ . '/components/add_cart.php';
require_once __DIR__ . '/components/add_wishlist.php';

// Fetch active products for featured sections
$menProducts = $conn->query("SELECT * FROM products WHERE status = 'active' AND (category = 'Men\'s Clothing' OR category = 'MEN' OR category = 'Men\'s Footwear' OR category = 'Men\'s Accessories') ORDER BY created_at DESC, id DESC LIMIT 4")->fetchAll();

$womenProducts = $conn->query("SELECT * FROM products WHERE status = 'active' AND (category = 'Women\'s Clothing' OR category = 'WOMEN' OR category = 'Western Dresses' OR category = 'Ethnic Wear' OR category = 'Women\'s Footwear' OR category = 'Women\'s Accessories') ORDER BY created_at DESC, id DESC LIMIT 4")->fetchAll();

$kidsProducts = $conn->query("SELECT * FROM products WHERE status = 'active' AND (category = 'Kids\' Clothing' OR category = 'KIDS' OR category = 'Baby & Toddler Wear' OR category = 'Kids\' Footwear' OR category = 'School Bags & Toys') ORDER BY created_at DESC, id DESC LIMIT 4")->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Fashion Store | Modern Style</title>
    <meta name="description" content="Shop modern clothing, ethnic wear, accessories and everyday fashion essentials.">
    <link rel="stylesheet" href="<?= e(app_url('css/user_style.css')) ?>?v=<?= filemtime(app_root_path().'/css/user_style.css') ?>">
    
    <style>
        /* HERO VIDEO BACKGROUND */
        .hero {
            position: relative;
            overflow: hidden;
        }
        .hero-video-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            transform: translate(-50%, -50%);
            object-fit: cover;
            z-index: 0;
        }
        .hero-video-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(100deg, rgba(14,14,14,0.7), rgba(14,14,14,0.45) 50%, rgba(14,14,14,0.2) 100%);
            z-index: 1;
            pointer-events: none;
        }
        .hero .container {
            position: relative;
            z-index: 2;
        }

        /* HORIZONTAL SCROLL SLIDER CSS */
        .promo-slider-container {
            margin-top: 3rem;
            width: 100%;
        }
        .promo-scroll-area {
            display: flex;
            overflow-x: auto;
            gap: 20px;
            padding-bottom: 15px;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            -ms-overflow-style: none; 
            scrollbar-width: none; 
        }
        .promo-scroll-area::-webkit-scrollbar {
            display: none; 
        }
        .promo-card {
            flex: 0 0 auto;
            width: 320px;
            scroll-snap-align: start;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .promo-card img {
            width: 100%;
            height: 320px;
            object-fit: cover;
            display: block;
            transition: transform 0.3s ease;
        }
        .promo-card:hover img {
            transform: scale(1.05);
        }

        /* PROMO BANNERS CSS */
        .promo-banners-container {
            margin-top: 3rem;
            display: flex;
            flex-direction: column;
            gap: 30px;
            width: 100%;
        }
        .banner-link {
            display: block;
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .banner-link:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .banner-link img {
            width: 100%;
            display: block;
            height: auto;
            object-fit: cover;
        }
        
        /* MAN HERO BANNER */
        .man-hero-banner {
            margin-top: 30px;
            width: 100%;
            min-height: 450px;
            background-image: url('images/men.jpg');
            background-size: cover;
            background-position: center left;
            background-repeat: no-repeat;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 40px 6%;
        }
        .man-hero-content {
            max-width: 450px;
            text-align: left;
            color: #222;
        }
        .man-eyebrow {
            display: inline-block;
            background: #222;
            color: #fff;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 18px;
            font-weight: 600;
        }
        .man-hero-content h2 {
            font-size: 2.6rem;
            line-height: 1.15;
            margin: 0 0 18px 0;
            font-weight: 800;
            color: #111;
        }
        .man-hero-content h2 span {
            color: #c9a961;
            font-style: italic;
        }
        .man-hero-content p {
            font-size: 1rem;
            line-height: 1.6;
            color: #555;
            margin-bottom: 25px;
        }
        .man-hero-btn {
            display: inline-block;
            background: #222;
            color: #fff;
            padding: 13px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid #222;
        }
        .man-hero-btn:hover {
            background: transparent;
            color: #222;
        }
        
        /* WOMEN SEASON SALE SLIDESHOW BANNER */
        .women-sale-banner {
            margin-top: 30px;
            width: 100%;
            min-height: 500px;
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .women-slide {
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center right;
            background-repeat: no-repeat;
            opacity: 0;
            animation: slideFade 20s infinite;
        }
        .women-slide:nth-child(1) {
            background-image: url('images/women1.jpg');
            animation-delay: 0s;
        }
        .women-slide:nth-child(2) {
            background-image: url('images/women2.jpg');
            animation-delay: 5s;
        }
        .women-slide:nth-child(3) {
            background-image: url('images/women3.jpg');
            animation-delay: 10s;
        }
        .women-slide:nth-child(4) {
            background-image: url('images/women4.jpg');
            animation-delay: 15s;
        }
        @keyframes slideFade {
            0%   { opacity: 0; transform: scale(1.05); }
            5%   { opacity: 1; transform: scale(1); }
            25%  { opacity: 1; transform: scale(1); }
            30%  { opacity: 0; transform: scale(1.05); }
            100% { opacity: 0; }
        }
        .women-sale-content {
            position: absolute;
            top: 50%;
            left: 6%;
            transform: translateY(-50%);
            max-width: 450px;
            z-index: 10;
            color: #fff;
            text-align: left;
            animation: fadeInLeft 1.2s ease;
        }
        .sale-tag {
            display: inline-block;
            background: #fff;
            color: #ff6b00;
            padding: 8px 18px;
            border-radius: 30px;
            font-size: 12px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 20px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .women-sale-content h2 {
            font-size: 3.5rem;
            line-height: 1;
            margin: 0 0 15px 0;
            font-weight: 900;
            color: #fff;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.2);
        }
        .women-sale-content .off-percent {
            font-size: 5rem;
            font-weight: 900;
            color: #fff;
            display: block;
            line-height: 1;
            margin: 10px 0;
            text-shadow: 3px 3px 10px rgba(0,0,0,0.25);
        }
        .women-sale-content .off-percent span {
            font-size: 2rem;
            vertical-align: top;
        }
        .women-sale-content p {
            font-size: 1.1rem;
            line-height: 1.5;
            color: #fff;
            margin-bottom: 25px;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.2);
        }
        .women-sale-btn {
            display: inline-block;
            background: #fff;
            color: #ff6b00;
            padding: 14px 34px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            border: 2px solid #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .women-sale-btn:hover {
            background: transparent;
            color: #fff;
        }
        @keyframes fadeInLeft {
            from { opacity: 0; transform: translate(-30px, -50%); }
            to { opacity: 1; transform: translate(0, -50%); }
        }
        
        /* Mobile adjustments */
        @media (max-width: 768px) {
            .promo-card {
                width: 260px;
            }
            .promo-card img {
                height: 260px;
            }
            .promo-banners-container {
                gap: 20px;
            }
        }
        @media (max-width: 900px) {
            .man-hero-banner {
                background-position: -80px center;
                justify-content: center;
                padding: 30px 20px;
            }
            .man-hero-content {
                background: rgba(255,255,255,0.9);
                padding: 25px;
                border-radius: 12px;
            }
            .man-hero-content h2 { font-size: 2rem; }
            
            .women-sale-banner { min-height: 420px; }
            .women-sale-content {
                background: rgba(0,0,0,0.35);
                padding: 25px;
                border-radius: 12px;
                backdrop-filter: blur(3px);
            }
            .women-sale-content h2 { font-size: 2.2rem; }
            .women-sale-content .off-percent { font-size: 3.5rem; }
        }
        @media (max-width: 600px) {
            .man-hero-banner { min-height: 380px; }
            .man-hero-content h2 { font-size: 1.6rem; }
            .women-sale-banner { min-height: 380px; }
            .women-sale-content h2 { font-size: 1.8rem; }
            .women-sale-content .off-percent { font-size: 2.8rem; }
            .women-sale-content p { font-size: 0.95rem; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/components/user_header.php'; ?>

<section class="hero">
    <video autoplay loop muted playsinline class="hero-video-bg">
        <source src="<?= e(app_url('images/main_banner_video.mp4')) ?>" type="video/mp4">
    </video>
    <div class="hero-video-overlay"></div>
    <div class="container hero-grid">
        <div>
            <h1>Elevate your everyday style.</h1>
            <p>Discover real fashion looks, trending essentials, and timeless pieces in one premium shopping experience designed for modern customers.</p>
            <div class="button-row">
                <a class="button" href="<?= e(app_url('shop.php')) ?>">Shop collection</a>
                <a class="button secondary" href="<?= e(app_url('about.php')) ?>">Our story</a>
            </div>
            <div class="hero-highlights">
                <span>Fresh arrivals</span>
                <span>Real product images</span>
                <span>Secure checkout</span>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">Featured</span><h2>Fresh arrivals</h2></div><p>Latest active products added by our fashion sellers.</p></div>
        
        <?php include __DIR__ . '/components/homeshop.php'; ?>
        
        <div class="button-row" style="justify-content:center"><a class="button secondary" href="<?= e(app_url('shop.php')) ?>">View all products</a></div>

        <!-- FEATURED: SHOP FOR MEN -->
        <div style="margin-top: 60px;">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">Featured</span>
                    <h2>Shop for Men</h2>
                </div>
                <p>Explore our curated collection of men's clothing and style essentials.</p>
            </div>
            
            <?php if ($menProducts): ?>
                <div class="product-grid">
                    <?php foreach ($menProducts as $product): ?>
                        <?php include __DIR__ . '/components/product_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state"><h3>No Men's products found</h3><p>Check back soon for new arrivals.</p></div>
            <?php endif; ?>

            <div class="button-row" style="justify-content:center; margin-top:24px;">
                <a class="button" href="<?= e(app_url('shop.php?category=Men\'s Clothing')) ?>">Shop Now →</a>
            </div>
        </div>
        <!-- END SHOP FOR MEN -->

        <!-- FEATURED: SHOP FOR WOMEN -->
        <div style="margin-top: 60px;">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">Featured</span>
                    <h2>Shop for Women</h2>
                </div>
                <p>Discover elegant dresses, ethnic wear, and modern everyday style for women.</p>
            </div>
            
            <?php if ($womenProducts): ?>
                <div class="product-grid">
                    <?php foreach ($womenProducts as $product): ?>
                        <?php include __DIR__ . '/components/product_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state"><h3>No Women's products found</h3><p>Check back soon for new arrivals.</p></div>
            <?php endif; ?>

            <div class="button-row" style="justify-content:center; margin-top:24px;">
                <a class="button" href="<?= e(app_url('shop.php?category=Women\'s Clothing')) ?>">Shop Now →</a>
            </div>
        </div>
        <!-- END SHOP FOR WOMEN -->

        <!-- FEATURED: SHOP FOR KIDS -->
        <div style="margin-top: 60px;">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">Featured</span>
                    <h2>Shop for Kids</h2>
                </div>
                <p>Cute, comfortable, and trendy outfits designed for kids and toddlers.</p>
            </div>
            
            <?php if ($kidsProducts): ?>
                <div class="product-grid">
                    <?php foreach ($kidsProducts as $product): ?>
                        <?php include __DIR__ . '/components/product_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state"><h3>No Kids' products found</h3><p>Check back soon for new arrivals.</p></div>
            <?php endif; ?>

            <div class="button-row" style="justify-content:center; margin-top:24px;">
                <a class="button" href="<?= e(app_url('shop.php?category=Kids\' Clothing')) ?>">Shop Now →</a>
            </div>
        </div>
        <!-- END SHOP FOR KIDS -->

        <!-- HORIZONTALLY SCROLLABLE IMAGES -->
        <div class="promo-slider-container">
            <div class="promo-scroll-area">
                <div class="promo-card"><img src="images/promo1.jpg" alt="Summer Steals"></div>
                <div class="promo-card"><img src="images/promo2.webp" alt="Lounge Pyjamas"></div>
                <div class="promo-card"><img src="images/promo3.webp" alt="Double Style"></div>
                <div class="promo-card"><img src="images/promo4.webp" alt="Comfort Shorts"></div>
                <div class="promo-card"><img src="images/promo5.webp" alt="Casual Joggers"></div>
                <div class="promo-card"><img src="images/promo6.webp" alt="Summer Vests"></div>
                <div class="promo-card"><img src="images/promo7.jpg" alt="New Arrivals"></div>
            </div>
        </div>







    </div>
</section>

<section class="section soft">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">Why shop here</span><h2>Simple, reliable shopping</h2></div></div>
        <div class="feature-grid">
            <div class="feature-card"><strong>Curated products</strong><p class="muted">Only active, in-stock fashion products are shown to customers.</p></div>
            <div class="feature-card"><strong>Secure accounts</strong><p class="muted">Protected login, CSRF validation, and safe password storage.</p></div>
            <div class="feature-card"><strong>Order tracking</strong><p class="muted">See order, delivery, and payment status directly from your account.</p></div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/components/user_footer.php'; ?>
<?php include __DIR__ . '/components/alert.php'; ?>
</body></html>
