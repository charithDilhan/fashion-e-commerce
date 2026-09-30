<?php
require_once __DIR__ . '/components/connect.php';
if ($user_id !== '') {
    redirect('profile.php');
}
$error = '';
if (is_post()) {
    $email = strtolower(clean_text((string) ($_POST['email'] ?? ''), 100));
    $password = (string) ($_POST['password'] ?? '');
    $stmt = $conn->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_matches($password, $user['password'])) {
        maybe_upgrade_password($conn, 'users', $user['id'], $password, $user['password']);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        flash('success', 'Welcome back, ' . $user['name'] . '.');
        redirect('home.php');
    }
    $error = 'Invalid email address or password.';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Customer Login | Fashion Store</title><link rel="stylesheet" href="<?= e(app_url('css/user_style.css')) ?>?v=<?= filemtime(app_root_path().'/css/user_style.css') ?>"></head><body>
<?php include __DIR__ . '/components/user_header.php'; ?>
<main class="auth-shell auth-creative"><div class="container"><div class="auth-layout"><aside class="auth-visual" style="--auth-image:url('https://images.pexels.com/photos/1488463/pexels-photo-1488463.jpeg?auto=compress&cs=tinysrgb&w=1600');"><div><img class="auth-brand" src="<?= e(app_url('assets/logo.svg')) ?>" alt="Fashion Store logo"></div><div></div></aside><section class="auth-panel"><form class="form-card narrow" method="post"><div class="split-heading"><div><span class="eyebrow">Customer account</span><h2>Login now</h2></div><span class="badge pending">Secure</span></div><p class="muted">Use your registered email address and password to continue.</p><?= csrf_field() ?><?php if ($error): ?><p class="badge canceled" style="margin-bottom:12px"><?= e($error) ?></p><?php endif; ?><div class="field"><label>Email</label><input type="email" name="email" required autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>"></div><div class="field"><label>Password</label><input type="password" name="password" required autocomplete="current-password"></div><button class="button" type="submit" style="width:100%;margin-top:18px">Login</button><p class="auth-links">New customer? <a href="<?= e(app_url('register.php')) ?>">Create an account</a></p><p class="auth-links"><a href="<?= e(app_url('admin/login.php')) ?>">Admin login</a></p></form></section></div></div></main>
<?php include __DIR__ . '/components/alert.php'; ?>
</body></html>

