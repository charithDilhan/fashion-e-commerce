<?php
require_once __DIR__ . '/components/connect.php';
if ($user_id !== '') {
    redirect('profile.php');
}
$errors = [];
if (is_post()) {
    $name = clean_text((string) ($_POST['name'] ?? ''), 50);
    $email = strtolower(clean_text((string) ($_POST['email'] ?? ''), 100));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');
    if (strlen($name) < 2) $errors[] = 'Name must contain at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8) $errors[] = 'Password must contain at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    $check = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $check->execute([$email]);
    if ($check->fetch()) $errors[] = 'A customer account already uses this email address.';
    if (!$errors) {
        try {
            $image = upload_image('image');
            $id = unique_id();
            $stmt = $conn->prepare('INSERT INTO users (id, name, email, password, image) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$id, $name, $email, password_hash($password, PASSWORD_DEFAULT), $image]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = $id;
            flash('success', 'Your account was created successfully.');
            redirect('home.php');
        } catch (Throwable $exception) {
            $errors[] = friendly_exception($exception, 'The account could not be created. Please try again.');
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Create Account | Fashion Store</title><link rel="stylesheet" href="<?= e(app_url('css/user_style.css')) ?>?v=<?= filemtime(app_root_path().'/css/user_style.css') ?>"></head><body>
<?php include __DIR__ . '/components/user_header.php'; ?>
<main class="auth-shell auth-creative"><div class="container"><div class="auth-layout"><aside class="auth-visual" style="--auth-image:url('https://images.pexels.com/photos/934070/pexels-photo-934070.jpeg?auto=compress&cs=tinysrgb&w=1600');"><div><img class="auth-brand" src="<?= e(app_url('assets/logo.svg')) ?>" alt="Fashion Store logo"></div><div></div></aside><section class="auth-panel"><form class="form-card narrow" method="post" enctype="multipart/form-data"><div class="split-heading"><div><span class="eyebrow">Join Fashion</span><h2>Create customer account</h2></div><span class="badge active">New</span></div><p class="muted">Enter your details below to get started.</p><?= csrf_field() ?><?php foreach ($errors as $item): ?><p class="badge canceled" style="margin-bottom:12px"><?= e($item) ?></p><?php endforeach; ?><div class="field"><label>Full name</label><input type="text" name="name" required maxlength="50" value="<?= e($_POST['name'] ?? '') ?>"></div><div class="field"><label>Email</label><input type="email" name="email" required maxlength="100" value="<?= e($_POST['email'] ?? '') ?>"></div><div class="field"><label>Password</label><input type="password" name="password" required minlength="8"></div><div class="field"><label>Confirm password</label><input type="password" name="confirm_password" required minlength="8"></div><div class="field"><label>Profile image <span class="muted">(optional)</span></label><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/avif"></div><button class="button" type="submit" style="width:100%;margin-top:18px">Create account</button><p class="auth-links">Already registered? <a href="<?= e(app_url('login.php')) ?>">Login</a></p></form></section></div></div></main>
<?php include __DIR__ . '/components/alert.php'; ?>
</body></html>

