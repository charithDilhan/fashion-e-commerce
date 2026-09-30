<?php
if (is_post() && isset($_POST['add_to_wishlist'])) {
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

    try {
        $product = fetch_product($conn, $productId, true);

        if (!$product) {
            flash('error', 'The selected product is unavailable.');
        } else {
            $exists = $conn->prepare('SELECT id FROM wishlist WHERE user_id = ? AND product_id = ? LIMIT 1');
            $exists->execute([$activeUserId, $productId]);
            if ($exists->fetch()) {
                flash('info', 'This product is already in your wishlist.');
            } else {
                $count = $conn->prepare('SELECT COUNT(*) FROM wishlist WHERE user_id = ?');
                $count->execute([$activeUserId]);
                if ((int) $count->fetchColumn() >= 50) {
                    flash('warning', 'Your wishlist is full.');
                } else {
                    $insert = $conn->prepare('INSERT INTO wishlist (id, user_id, product_id, price) VALUES (?, ?, ?, ?)');
                    $insert->execute([unique_id(), $activeUserId, $productId, $product['price']]);
                    flash('success', 'Product saved to your wishlist.');
                }
            }
        }
    } catch (PDOException $exception) {
        if ((string) $exception->getCode() === '23000') {
            [$GLOBALS['user_id'], $GLOBALS['seller_id']] = synchronise_auth_sessions($conn);
            flash('error', 'Your account or the selected product changed. Please refresh and try again.');
        } else {
            flash('error', friendly_exception($exception, 'The wishlist could not be updated. Please try again.'));
        }
    } catch (Throwable $exception) {
        flash('error', friendly_exception($exception, 'The wishlist could not be updated. Please try again.'));
    }

    redirect($returnTo);
}
