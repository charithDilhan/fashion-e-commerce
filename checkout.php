<?php
require_once __DIR__ . '/components/connect.php';
require_user();
$user = current_user($conn);
if (!$user) {
    unset($_SESSION['user_id']);
    $GLOBALS['user_id'] = '';
    flash('warning', 'Your login session expired. Please login again.');
    redirect('login.php');
}
$user_id = (string) $user['id'];
$cartStmt = $conn->prepare("SELECT c.id AS cart_id, c.qty, c.option_size, p.* FROM cart c JOIN products p ON p.id = c.product_id WHERE c.user_id = ? AND p.status = 'active' ORDER BY c.created_at ASC");
$cartStmt->execute([$user_id]);
$items = $cartStmt->fetchAll();
if (!$items) {
    flash('warning', 'Your cart is empty.');
    redirect('cart.php');
}
$errors = [];
$total = array_reduce($items, fn($sum,$item) => $sum + (float)$item['price']*(int)$item['qty'], 0.0);

$validMethods = [
    'Cash on delivery',
    'Credit/Debit Card (Dummy Gateway)'
];

if (is_post() && isset($_POST['place_order'])) {
    $name = clean_text((string)($_POST['name'] ?? ''), 50);
    $phone = clean_text((string)($_POST['phone'] ?? ''), 20);
    $email = strtolower(clean_text((string)($_POST['email'] ?? ''), 100));
    $address = clean_text((string)($_POST['address'] ?? ''), 255);
    $addressType = clean_text((string)($_POST['address_type'] ?? 'Home'), 50);
    $method = clean_text((string)($_POST['method'] ?? 'Cash on delivery'), 50);
    
    $cardNumber = clean_text((string)($_POST['card_number'] ?? ''), 30);

    if (strlen($name)<2) $errors[]='Enter your full name.';
    if (!preg_match('/^(?:\+94|0)?7\d{8}$/', preg_replace('/\s+/', '', $phone))) $errors[]='Enter a valid Sri Lankan mobile number.';
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Enter a valid email address.';
    if (strlen($address)<10) $errors[]='Enter a complete delivery address.';
    if (!in_array($addressType, ['Home', 'Office', 'Other'], true)) $errors[]='Select a valid address type.';
    if (!in_array($method, $validMethods, true)) $errors[]='Select a valid payment method.';

    if ($method === 'Credit/Debit Card (Dummy Gateway)') {
        $cleanCard = preg_replace('/\D/', '', $cardNumber);
        if (strlen($cleanCard) > 0 && strlen($cleanCard) < 12) {
            $errors[] = 'Enter a valid dummy card number.';
        }
    }

    if (!$errors) {
        try {
            $conn->beginTransaction();
            $refresh = $conn->prepare("SELECT c.qty, c.option_size, p.* FROM cart c JOIN products p ON p.id=c.product_id WHERE c.user_id=? AND p.status='active' FOR UPDATE");
            $refresh->execute([$user_id]);
            $lockedItems = $refresh->fetchAll();
            if (count($lockedItems)!==count($items)) throw new RuntimeException('Your cart changed. Please review it again.');
            
            $paymentStatus = ($method === 'Credit/Debit Card (Dummy Gateway)') ? 'paid' : 'pending';

            $insert = $conn->prepare('INSERT INTO orders (id,user_id,seller_id,name,number,email,address,address_type,method,product_id,price,qty,option_size,status,payment_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $stockUpdate = $conn->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
            foreach ($lockedItems as $item) {
                if ((int)$item['stock'] < (int)$item['qty']) throw new RuntimeException($item['name'].' has insufficient stock.');
                $stockUpdate->execute([(int)$item['qty'],$item['id'],(int)$item['qty']]);
                if ($stockUpdate->rowCount()!==1) throw new RuntimeException('Stock changed during checkout. Please try again.');
                $insert->execute([
                    unique_id(),
                    $user_id,
                    $item['seller_id'],
                    $name,
                    $phone,
                    $email,
                    $address,
                    $addressType,
                    $method,
                    $item['id'],
                    $item['price'],
                    (int)$item['qty'],
                    $item['option_size'] ?? '',
                    'in progress',
                    $paymentStatus
                ]);
            }
            $clear = $conn->prepare('DELETE FROM cart WHERE user_id = ?');
            $clear->execute([$user_id]);
            $conn->commit();

            if ($paymentStatus === 'paid') {
                flash('success', 'Payment successful (Dummy Gateway) & order placed!');
            } else {
                flash('success', 'Order placed successfully.');
            }
            redirect('order.php');
        } catch (Throwable $exception) {
            if ($conn->inTransaction()) $conn->rollBack();
            $errors[] = friendly_exception($exception, 'The order could not be placed. Please review your cart and try again.');
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Checkout | Fashion Store</title>
    <link rel="stylesheet" href="<?= e(app_url('css/user_style.css')) ?>?v=<?= filemtime(app_root_path().'/css/user_style.css') ?>">
    <style>
        .dummy-payment-box {
            margin-top: 15px;
            padding: 20px;
            border-radius: 12px;
            background: #f8f9fa;
            border: 1px dashed #c9a961;
            grid-column: 1 / -1;
            display: none;
        }
        .dummy-payment-box.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        .dummy-card-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 12px;
        }
        .dummy-card-grid .full {
            grid-column: 1 / -1;
        }
        .dummy-badge {
            display: inline-block;
            background: #e8f4ea;
            color: #1b5e20;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .fill-test-btn {
            font-size: 0.8rem;
            padding: 6px 12px;
            background: #222;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            float: right;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/components/user_header.php'; ?>

<section class="page-hero">
    <div class="container">
        <h1>Checkout</h1>
        <p>Confirm delivery and payment details.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="two-column">
            <form class="form-card" style="margin:0;max-width:none" method="post">
                <h2>Delivery & Payment details</h2>
                <?= csrf_field() ?>
                
                <?php foreach($errors as $item): ?>
                    <p class="badge canceled"><?= e($item) ?></p>
                <?php endforeach; ?>
                
                <div class="form-grid">
                    <div class="field">
                        <label>Full name</label>
                        <input name="name" required value="<?= e($_POST['name'] ?? $user['name']) ?>">
                    </div>
                    <div class="field">
                        <label>Mobile number</label>
                        <input name="phone" required placeholder="0771234567" value="<?= e($_POST['phone'] ?? '') ?>">
                    </div>
                    <div class="field full">
                        <label>Email</label>
                        <input type="email" name="email" required value="<?= e($_POST['email'] ?? $user['email']) ?>">
                    </div>
                    <div class="field full">
                        <label>Delivery address</label>
                        <textarea name="address" required><?= e($_POST['address'] ?? '') ?></textarea>
                    </div>
                    <div class="field">
                        <label>Address type</label>
                        <select name="address_type">
                            <?php foreach (['Home','Office','Other'] as $option): ?>
                                <option value="<?= e($option) ?>" <?= ($_POST['address_type'] ?? 'Home') === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Payment method</label>
                        <select name="method" id="payment_method">
                            <?php foreach ($validMethods as $option): ?>
                                <option value="<?= e($option) ?>" <?= ($_POST['method'] ?? 'Cash on delivery') === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- DUMMY CREDIT CARD GATEWAY SECTION -->
                    <div class="dummy-payment-box" id="card_dummy_box">
                        <button type="button" class="fill-test-btn" id="fill_test_card">Auto-fill Test Card</button>
                        <span class="dummy-badge">💳 Dummy Card Sandbox (Test Mode)</span>
                        <p style="margin: 4px 0 12px; font-size: 0.85rem; color: #666;">
                            Simulate instant card payment. No real money will be charged.
                        </p>
                        <div class="dummy-card-grid">
                            <div class="full">
                                <label style="font-size: 0.85rem; font-weight:600;">Card Number</label>
                                <input type="text" name="card_number" id="card_number" placeholder="4532 0123 4567 8910" style="width:100%; padding:9px 12px; border:1px solid #ccc; border-radius:8px;">
                            </div>
                            <div>
                                <label style="font-size: 0.85rem; font-weight:600;">Expiry Date</label>
                                <input type="text" name="card_expiry" id="card_expiry" placeholder="12/28" style="width:100%; padding:9px 12px; border:1px solid #ccc; border-radius:8px;">
                            </div>
                            <div>
                                <label style="font-size: 0.85rem; font-weight:600;">CVV / CVC</label>
                                <input type="text" name="card_cvv" id="card_cvv" placeholder="123" maxlength="4" style="width:100%; padding:9px 12px; border:1px solid #ccc; border-radius:8px;">
                            </div>
                        </div>
                    </div>
                </div>

                <button class="button" style="margin-top:20px; width:100%; font-size:1.05rem;" type="submit" name="place_order">Place order</button>
            </form>

            <aside class="summary-card">
                <h3>Your order</h3>
                <?php foreach($items as $item): ?>
                    <div class="summary-row">
                        <span><?= e($item['name']) ?><?= !empty($item['option_size']) ? ' (Size: '.e($item['option_size']).')' : '' ?> × <?= (int)$item['qty'] ?></span>
                        <strong><?= e(money((float)$item['price']*(int)$item['qty'])) ?></strong>
                    </div>
                <?php endforeach; ?>
                <div class="summary-row total">
                    <span>Total</span>
                    <span><?= e(money($total)) ?></span>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php include __DIR__ . '/components/user_footer.php'; include __DIR__ . '/components/alert.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const methodSelect = document.getElementById('payment_method');
    const cardBox = document.getElementById('card_dummy_box');
    const fillBtn = document.getElementById('fill_test_card');

    function togglePaymentBoxes() {
        if (methodSelect.value === 'Credit/Debit Card (Dummy Gateway)') {
            cardBox.classList.add('active');
        } else {
            cardBox.classList.remove('active');
        }
    }

    methodSelect.addEventListener('change', togglePaymentBoxes);
    togglePaymentBoxes();

    fillBtn.addEventListener('click', function() {
        document.getElementById('card_number').value = '4532 8910 2345 6789';
        document.getElementById('card_expiry').value = '12/28';
        document.getElementById('card_cvv').value = '321';
    });
});
</script>
</body>
</html>
