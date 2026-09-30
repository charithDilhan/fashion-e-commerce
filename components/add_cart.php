<?php
if (is_post() && isset($_POST['add_to_cart'])) {
    $returnTo = clean_text((string) ($_POST['return_to'] ?? 'shop.php'), 120);
    if (!preg_match('/^[a-zA-Z0-9_\-\.]+(?:\?[a-zA-Z0-9_=&%\-\.]*)?$/', $returnTo)) {
        $returnTo = 'shop.php';
    }

    $authenticatedUser = current_user($conn);
    if (!$authenticatedUser) {
        flash('warning', 'Your login session is no longer valid. Please login again.');
        redirect('login.php');
    }
    $activeUserId = (string) $authenticatedUser['id'];

    $productId = clean_text((string) ($_POST['product_id'] ?? ''), 20);
    $qty = max(1, min(99, (int) ($_POST['qty'] ?? 1)));
    $optionSize = clean_text((string) ($_POST['option_size'] ?? ''), 50);

    try {
        $product = fetch_product($conn, $productId, true);

        if (!$product) {
            flash('error', 'The selected product is unavailable.');
        } elseif ((int) $product['stock'] < $qty) {
            flash('warning', 'Only ' . (int) $product['stock'] . ' item(s) are currently available.');
        } else {
            // Validate size requirement if product has sizes listed
            $availableSizes = parse_product_sizes((string) ($product['product_detail'] ?? ''));
            if ($availableSizes && $optionSize === '') {
                $optionSize = $availableSizes[0];
            }

            $check = $conn->prepare('SELECT id, qty FROM cart WHERE user_id = ? AND product_id = ? LIMIT 1');
            $check->execute([$activeUserId, $productId]);
            $existing = $check->fetch();

            if ($existing) {
                $newQty = min((int) $product['stock'], (int) $existing['qty'] + $qty);
                $update = $conn->prepare('UPDATE cart SET qty = ?, price = ?, option_size = ? WHERE id = ? AND user_id = ?');
                $update->execute([$newQty, $product['price'], $optionSize, $existing['id'], $activeUserId]);
                flash('success', 'Cart updated with selected size.');
            } else {
                $count = $conn->prepare('SELECT COUNT(*) FROM cart WHERE user_id = ?');
                $count->execute([$activeUserId]);
                if ((int) $count->fetchColumn() >= 20) {
                    flash('warning', 'Your cart can contain up to 20 different products.');
                } else {
                    $insert = $conn->prepare('INSERT INTO cart (id, user_id, product_id, price, qty, option_size) VALUES (?, ?, ?, ?, ?, ?)');
                    $insert->execute([unique_id(), $activeUserId, $productId, $product['price'], $qty, $optionSize]);
                    $removeWishlist = $conn->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?');
                    $removeWishlist->execute([$activeUserId, $productId]);
                    flash('success', 'Product added to your cart.');
                }
            }
        }
    } catch (PDOException $exception) {
        if ((string) $exception->getCode() === '23000') {
            [$GLOBALS['user_id'], $GLOBALS['seller_id']] = synchronise_auth_sessions($conn);
            flash('error', 'Your account or the selected product changed. Please refresh and try again.');
        } else {
            flash('error', friendly_exception($exception, 'The cart could not be updated. Please try again.'));
        }
    } catch (Throwable $exception) {
        flash('error', friendly_exception($exception, 'The cart could not be updated. Please try again.'));
    }

    redirect($returnTo);
}
