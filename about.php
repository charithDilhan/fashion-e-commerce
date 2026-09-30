<?php
require_once __DIR__ . '/components/connect.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>About | Fashion Store</title>
    <link rel="stylesheet" href="<?= e(app_url('css/user_style.css')) ?>?v=<?= filemtime(app_root_path().'/css/user_style.css') ?>">
    <style>
        /* SERVICES ICONS SECTION */
        .services-section {
            padding: 60px 0;
            background: linear-gradient(135deg, #fff5f0 0%, #fef8ff 100%);
        }
        .services-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-top: 40px;
        }
        .service-card {
            background: #fff;
            border-radius: 16px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            transition: all 0.4s ease;
            border: 1px solid #f0f0f0;
        }
        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }
        .service-icon {
            width: 90px;
            height: 90px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff8f5;
            border-radius: 50%;
            padding: 15px;
            transition: transform 0.4s ease;
        }
        .service-card:hover .service-icon {
            transform: scale(1.1) rotate(5deg);
        }
        .service-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .service-card h3 {
            font-size: 1.15rem;
            color: #222;
            margin: 0 0 10px 0;
            font-weight: 700;
        }
        .service-card p {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.5;
            margin: 0;
        }
        
        /* Responsive */
        @media (max-width: 900px) {
            .services-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 500px) {
            .services-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/components/user_header.php'; ?>

<section class="hero" style="--hero-image:url('https://images.pexels.com/photos/1926769/pexels-photo-1926769.jpeg?auto=compress&cs=tinysrgb&w=1600');">
    <div class="container hero-grid">
        <div>
            <span class="eyebrow">About Fashion</span>
            <h1>Style, simplicity, and trust in one place.</h1>
            <p>Fashion Store connects customers with independent fashion sellers through one clear, secure, and responsive shopping experience.</p>
            <div class="button-row">
                <a class="button" href="<?= e(app_url('shop.php')) ?>">Explore shop</a>
                <a class="button secondary" href="<?= e(app_url('contact.php')) ?>">Contact us</a>
            </div>
            <div class="hero-highlights">
                <span>Responsive design</span>
                <span>Seller dashboard</span>
                <span>Customer-friendly flow</span>
            </div>
        </div>
    </div>
</section>

<!-- NEW SERVICES ICONS SECTION -->
<section class="services-section">
    <div class="container">
        <div class="section-heading" style="text-align:center;">
            <div>
                <span class="eyebrow">What We Offer</span>
                <h2>Our Services & Features</h2>
            </div>
            <p>Everything you need for a seamless online fashion shopping experience.</p>
        </div>
        
        <div class="services-grid">
            
            <!-- Fast Delivery -->
            <div class="service-card">
                <div class="service-icon">
                    <img src="images/icon_delivery.png" alt="Fast Delivery">
                </div>
                <h3>Fast Delivery</h3>
                <p>Quick and reliable doorstep delivery for all your fashion orders across the country.</p>
            </div>
            
            <!-- Latest Fashion -->
            <div class="service-card">
                <div class="service-icon">
                    <img src="images/icon_fashion.png" alt="Latest Fashion">
                </div>
                <h3>Latest Fashion</h3>
                <p>Discover trending styles, premium clothing, and accessories at unbeatable prices.</p>
            </div>
            
            <!-- Easy Online Order -->
            <div class="service-card">
                <div class="service-icon">
                    <img src="images/icon_order.png" alt="Easy Online Order">
                </div>
                <h3>Easy Online Order</h3>
                <p>Simple, smooth, and secure ordering experience right from your smartphone or laptop.</p>
            </div>
            
            <!-- 24/7 Customer Service -->
            <div class="service-card">
                <div class="service-icon">
                    <img src="images/icon_support.png" alt="Customer Support">
                </div>
                <h3>24/7 Customer Support</h3>
                <p>Our friendly support team is always ready to help you with any questions or issues.</p>
            </div>
            
        </div>
    </div>
</section>
<!-- END SERVICES ICONS SECTION -->

<section class="section">
    <div class="container">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Our promise</span>
                <h2>A complete marketplace flow</h2>
            </div>
            <p>From product discovery to checkout and order management, every key step is connected.</p>
        </div>
        <div class="feature-grid">
            <div class="feature-card"><strong>For customers</strong><p class="muted">Search, wishlist, cart, checkout, profile updates, and order tracking.</p></div>
            <div class="feature-card"><strong>For sellers</strong><p class="muted">Product CRUD, stock control, order processing, customer messages, and dashboard reports.</p></div>
            <div class="feature-card"><strong>Built for any folder</strong><p class="muted">Dynamic paths prevent broken links when the project folder name changes.</p></div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/components/user_footer.php'; include __DIR__ . '/components/alert.php'; ?>
</body>
</html>
