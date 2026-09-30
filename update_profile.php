<?php
require_once __DIR__ . '/components/connect.php';
require_user();
$user = current_user($conn);
$errors = [];
if (is_post()) {
    $name = clean_text((string) ($_POST['name'] ?? ''), 50);
    $email = strtolower(clean_text((string) ($_POST['email'] ?? ''), 100));
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');
    if (strlen($name) < 2) $errors[] = 'Name must contain at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    $duplicate = $conn->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
    $duplicate->execute([$email, $user_id]);
    if ($duplicate->fetch()) $errors[] = 'Another customer account already uses that email.';
    if ($newPassword !== '') {
        if (!password_matches($currentPassword, $user['password'])) $errors[] = 'Current password is incorrect.';
        if (strlen($newPassword) < 8) $errors[] = 'New password must contain at least 8 characters.';
        if ($newPassword !== $confirm) $errors[] = 'New passwords do not match.';
    }
    if (!$errors) {
        try {
            $image = upload_image('image', (string) $user['image']);
            $password = $newPassword !== '' ? password_hash($newPassword, PASSWORD_DEFAULT) : $user['password'];
            $stmt = $conn->prepare('UPDATE users SET name = ?, email = ?, password = ?, image = ? WHERE id = ?');
            $stmt->execute([$name, $email, $password, $image, $user_id]);
            flash('success', 'Profile updated successfully.');
            redirect('profile.php');
        } catch (Throwable $exception) {
            $errors[] = friendly_exception($exception, 'Your profile could not be updated. Please try again.');
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Edit Profile | Fashion Store</title><link rel="stylesheet" href="<?= e(app_url('css/user_style.css')) ?>?v=<?= filemtime(app_root_path().'/css/user_style.css') ?>"></head><body>
<?php include __DIR__ . '/components/user_header.php'; ?>
<section class="section"><div class="container"><form class="form-card" method="post" enctype="multipart/form-data"><h2>Edit profile</h2><p class="muted">Leave password fields empty to keep your current password.</p><?= csrf_field() ?><?php foreach ($errors as $item): ?><p class="badge canceled"><?= e($item) ?></p><?php endforeach; ?><div class="form-grid"><div class="field"><label>Full name</label><input name="name" required value="<?= e($_POST['name'] ?? $user['name']) ?>"></div><div class="field"><label>Email</label><input type="email" name="email" required value="<?= e($_POST['email'] ?? $user['email']) ?>"></div><div class="field full"><label>New profile image <span class="muted">(optional)</span></label><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/avif"></div><div class="field"><label>Current password</label><input type="password" name="current_password"></div><div class="field"><label>New password</label><input type="password" name="new_password" minlength="8"></div><div class="field"><label>Confirm new password</label><input type="password" name="confirm_password" minlength="8"></div></div><div class="button-row"><button class="button" type="submit">Save changes</button><a class="button secondary" href="<?= e(app_url('profile.php')) ?>">Cancel</a></div></form></div></section>
<?php include __DIR__ . '/components/user_footer.php'; include __DIR__ . '/components/alert.php'; ?>
</body></html>

