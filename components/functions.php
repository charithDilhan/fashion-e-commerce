<?php

declare(strict_types=1);

function app_root_path(): string
{
    return dirname(__DIR__);
}

function app_base_url(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
    $projectRoot = realpath(app_root_path()) ?: app_root_path();
    $documentRoot = str_replace('\\', '/', $documentRoot);
    $projectRoot = str_replace('\\', '/', $projectRoot);

    if ($documentRoot !== '' && str_starts_with(strtolower($projectRoot), strtolower($documentRoot))) {
        $base = '/' . trim(substr($projectRoot, strlen($documentRoot)), '/');
        if ($base === '/') {
            $base = '';
        }
    } else {
        $base = '';
    }

    return $base;
}

function app_url(string $path = ''): string
{
    $path = ltrim($path, '/');
    $baseUrl = app_base_url();
    
    // Localhost එකේ වැඩ කරද්දී 'fashion_web' folder එක URL එකට හරියටම ලින්ක් කරනවා
    if ($baseUrl === '' || $baseUrl === '/') {
        $baseUrl = '/fashion_web';
    }
    
    return $baseUrl . ($path !== '' ? '/' . $path : '');
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function unique_id(): string
{
    return substr(bin2hex(random_bytes(16)), 0, 20);
}

function redirect(string $path): never
{
    header('Location: ' . app_url($path));
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    if (!is_post()) {
        return;
    }

    $submitted = (string) ($_POST['csrf_token'] ?? '');
    if ($submitted === '' || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(419);
        exit('Your session expired. Please go back, refresh the page, and try again.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flashes'][] = ['type' => $type, 'message' => $message];
}

function pull_flashes(): array
{
    $flashes = $_SESSION['flashes'] ?? [];
    unset($_SESSION['flashes']);
    return is_array($flashes) ? $flashes : [];
}

function require_user(): void
{
    $id = (string) ($GLOBALS['user_id'] ?? $_SESSION['user_id'] ?? '');
    if ($id === '') {
        flash('warning', 'Please login to continue.');
        redirect('login.php');
    }
}

function require_seller(): void
{
    $id = (string) ($GLOBALS['seller_id'] ?? $_SESSION['seller_id'] ?? '');
    if ($id === '') {
        flash('warning', 'Please login to the admin panel.');
        redirect('admin/login.php');
    }
}

/**
 * Validate authentication IDs stored in the PHP session against the database.
 * This prevents deleted or old browser sessions from being used as foreign keys.
 */
function synchronise_auth_sessions(PDO $conn): array
{
    $resolved = ['user_id' => '', 'seller_id' => ''];
    $invalidated = false;
    $sources = ['user_id' => 'users', 'seller_id' => 'sellers'];

    foreach ($sources as $sessionKey => $table) {
        $raw = $_SESSION[$sessionKey] ?? '';
        $id = is_scalar($raw) ? trim((string) $raw) : '';

        if ($id === '' || !preg_match('/^[A-Za-z0-9_-]{1,20}$/', $id)) {
            if (array_key_exists($sessionKey, $_SESSION)) {
                unset($_SESSION[$sessionKey]);
                $invalidated = true;
            }
            continue;
        }

        $stmt = $conn->prepare("SELECT id FROM `{$table}` WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $storedId = $stmt->fetchColumn();

        if ($storedId === false) {
            unset($_SESSION[$sessionKey]);
            $invalidated = true;
            continue;
        }

        $resolved[$sessionKey] = (string) $storedId;
        $_SESSION[$sessionKey] = (string) $storedId;
    }

    if ($invalidated && session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
        session_regenerate_id(true);
    }

    return [$resolved['user_id'], $resolved['seller_id']];
}

function friendly_exception(Throwable $exception, string $fallback): string
{
    error_log('[Fashion Store] ' . $exception::class . ': ' . $exception->getMessage());
    return $exception instanceof RuntimeException && !($exception instanceof PDOException)
        ? $exception->getMessage()
        : $fallback;
}

function password_matches(string $plain, string $stored): bool
{
    if (password_get_info($stored)['algo'] !== null) {
        return password_verify($plain, $stored);
    }

    // Backward compatibility with the SHA-1 passwords in the original database dump.
    return hash_equals($stored, sha1($plain));
}

function maybe_upgrade_password(PDO $conn, string $table, string $id, string $plain, string $stored): void
{
    if (password_get_info($stored)['algo'] === null && hash_equals($stored, sha1($plain))) {
        $hash = password_hash($plain, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE `{$table}` SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $id]);
    }
}

function clean_text(string $value, int $max = 255): string
{
    $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    return function_exists('mb_substr') ? mb_substr($value, 0, $max) : substr($value, 0, $max);
}

function product_categories(): array
{
    return [
        "Women's Clothing",
        "Men's Clothing",
        "Kids' Clothing",
        "Baby & Toddler Wear",
        'Ethnic Wear',
        'Western Dresses',
        "Men's Footwear",
        "Women's Footwear",
        "Kids' Footwear",
        "Men's Accessories",
        "Women's Accessories",
        'Handbags & Purses',
        'School Bags & Toys',
        'Other',
    ];
}

function upload_image(string $field, string $fallback = ''): string
{
    if (!isset($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $fallback;
    }

    $file = $_FILES[$field];
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed. Please try another image.');
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Each image must be smaller than 5 MB.');
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp) ?: '';
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
    ];

    if (!isset($extensions[$mime])) {
        throw new RuntimeException('Only JPG, PNG, WEBP, and AVIF images are allowed.');
    }

    $directory = app_root_path() . '/uploaded_files';
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('Upload folder could not be created.');
    }

    $name = bin2hex(random_bytes(12)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($tmp, $directory . '/' . $name)) {
        throw new RuntimeException('Image could not be saved.');
    }

    return $name;
}

function uploaded_image_url(?string $filename, string $kind = 'product'): string
{
    $filename = basename((string) $filename);
    if ($filename !== '' && is_file(app_root_path() . '/uploaded_files/' . $filename)) {
        return app_url('uploaded_files/' . rawurlencode($filename));
    }

    return app_url($kind === 'avatar' ? 'assets/avatar.svg' : 'assets/product-placeholder.svg');
}

function money(mixed $amount): string
{
    return 'LKR ' . number_format((float) $amount, 2);
}

function current_user(PDO $conn): ?array
{
    $id = (string) ($GLOBALS['user_id'] ?? $_SESSION['user_id'] ?? '');
    if ($id === '') {
        return null;
    }

    $stmt = $conn->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if (!$user) {
        unset($_SESSION['user_id']);
        $GLOBALS['user_id'] = '';
    }

    return $user;
}

function current_seller(PDO $conn): ?array
{
    $id = (string) ($GLOBALS['seller_id'] ?? $_SESSION['seller_id'] ?? '');
    if ($id === '') {
        return null;
    }

    $stmt = $conn->prepare('SELECT * FROM sellers WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if (!$seller) {
        unset($_SESSION['seller_id']);
        $GLOBALS['seller_id'] = '';
    }

    return $seller;
}

function fetch_product(PDO $conn, string $id, bool $activeOnly = false): ?array
{
    $sql = 'SELECT p.*, s.name AS seller_name FROM products p LEFT JOIN sellers s ON s.id = p.seller_id WHERE p.id = ?';
    if ($activeOnly) {
        $sql .= " AND p.status = 'active'";
    }
    $sql .= ' LIMIT 1';
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function cart_count(PDO $conn, string $userId): int
{
    if ($userId === '') {
        return 0;
    }
    $stmt = $conn->prepare("SELECT COALESCE(SUM(c.qty), 0) FROM cart c JOIN products p ON p.id = c.product_id WHERE c.user_id = ? AND p.status = 'active'");
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

function wishlist_count(PDO $conn, string $userId): int
{
    if ($userId === '') {
        return 0;
    }
    $stmt = $conn->prepare("SELECT COUNT(*) FROM wishlist w JOIN products p ON p.id = w.product_id WHERE w.user_id = ? AND p.status = 'active'");
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}


/**
 * The built-in System Admin account is a store-wide administrator.
 * Existing seller accounts remain limited to their own products and orders.
 */
function is_system_admin(PDO $conn): bool
{
    $seller = current_seller($conn);
    if (!$seller) {
        return false;
    }

    return (string) ($seller['id'] ?? '') === 'ADM1N000000000000001'
        || strtolower((string) ($seller['email'] ?? '')) === 'admin@fashionstore.lk';
}

function can_manage_seller_record(PDO $conn, string $ownerSellerId): bool
{
    return is_system_admin($conn)
        || hash_equals((string) ($_SESSION['seller_id'] ?? ''), $ownerSellerId);
}

function parse_product_sizes(string $productDetail): array
{
    $sizes = [];
    if (preg_match_all('/(?:\[|\()([A-Za-z0-9\.\-\+\/\s]+)(?:\]|\))/', $productDetail, $matches)) {
        if (!empty($matches[1])) {
            foreach ($matches[1] as $sz) {
                $sz = trim($sz);
                if ($sz !== '' && !in_array($sz, $sizes, true)) {
                    $sizes[] = $sz;
                }
            }
        }
    }

    if (empty($sizes)) {
        if (preg_match('/Size[s]?\s*:\s*([^\n\r]+)/i', $productDetail, $match)) {
            $rawSizes = trim($match[1]);
            $parts = preg_split('/[\s,\|]+/', $rawSizes);
            foreach ($parts as $part) {
                $part = trim($part, " \t\n\r\0\x0B[]()");
                if ($part !== '' && !in_array($part, $sizes, true)) {
                    $sizes[] = $part;
                }
            }
        }
    }

    return $sizes;
}

function clean_product_detail(string $productDetail): string
{
    $cleaned = preg_replace('/(?:\r?\n)?(?:Size[s]?\s*:\s*)?(?:(?:\[|\()[A-Za-z0-9\.\-\+\/\s]+(?:\]|\))\s*)+(?:\r?\n)?/i', '', $productDetail);
    $cleaned = preg_replace('/(?:\r?\n)?Size[s]?\s*:\s*[^\r\n]+(?:\r?\n)?/i', '', $cleaned);
    return trim($cleaned);
}