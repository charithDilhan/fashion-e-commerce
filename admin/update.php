<?php
require_once __DIR__ . '/../components/connect.php';
require_seller();
$seller = current_seller($conn);
$errors = [];
if (is_post() && isset($_POST['update_profile'])) {
    $name = clean_text((string) ($_POST['name'] ?? ''), 50);
    $email = strtolower(clean_text((string) ($_POST['email'] ?? ''), 100));
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if (strlen($name) < 2) $errors[] = 'Name must contain at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    $duplicate = $conn->prepare('SELECT id FROM sellers WHERE email = ? AND id <> ? LIMIT 1');
    $duplicate->execute([$email, $seller_id]);
    if ($duplicate->fetch()) $errors[] = 'Another seller already uses that email.';

    if ($newPassword !== '') {
        if (!password_matches($currentPassword, $seller['password'])) $errors[] = 'Current password is incorrect.';
        if (strlen($newPassword) < 8) $errors[] = 'New password must contain at least 8 characters.';
        if ($newPassword !== $confirm) $errors[] = 'New passwords do not match.';
    }

    if (!$errors) {
        try {
            $image = upload_image('image', (string) $seller['image']);
            $password = $newPassword !== '' ? password_hash($newPassword, PASSWORD_DEFAULT) : $seller['password'];
            $stmt = $conn->prepare('UPDATE sellers SET name = ?, email = ?, password = ?, image = ? WHERE id = ?');
            $stmt->execute([$name, $email, $password, $image, $seller_id]);
            flash('success', 'Admin profile updated.');
            redirect('admin/profile.php');
        } catch (Throwable $exception) {
            $errors[] = friendly_exception($exception, 'The admin profile could not be updated. Please try again.');
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Edit Admin Profile</title><link rel="stylesheet" href="<?= e(app_url('css/admin_style.css')) ?>"></head><body>
<?php include __DIR__ . '/../components/admin_header.php'; ?>
<main class="admin-main"><div class="admin-container"><div class="admin-page-heading"><div><h1>Edit profile</h1><p>Leave password fields empty to keep the current password.</p></div></div><form class="panel" method="post" enctype="multipart/form-data"><?= csrf_field() ?><?php foreach ($errors as $item): ?><p class="badge canceled"><?= e($item) ?></p><?php endforeach; ?><div class="form-grid"><div class="field"><label>Name</label><input name="name" required value="<?= e($_POST['name'] ?? $seller['name']) ?>"></div><div class="field"><label>Email</label><input type="email" name="email" required value="<?= e($_POST['email'] ?? $seller['email']) ?>"></div><div class="field full"><label>New profile image (optional)</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/avif"></div><div class="field"><label>Current password</label><input type="password" name="current_password"></div><div class="field"><label>New password</label><input type="password" name="new_password" minlength="8"></div><div class="field"><label>Confirm new password</label><input type="password" name="confirm_password" minlength="8"></div></div><div style="display:flex;gap:10px;margin-top:20px"><button class="button" type="submit" name="update_profile">Save changes</button><a class="button secondary" href="<?= e(app_url('admin/profile.php')) ?>">Cancel</a></div></form></div></main>
<?php include __DIR__ . '/../components/admin_footer.php'; include __DIR__ . '/../components/alert.php'; ?>
</body></html>
