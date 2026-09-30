<?php
require_once __DIR__ . '/../components/connect.php';
if ($seller_id !== '') redirect('admin/dashboard.php');
$error='';
if(is_post()){
    $email=strtolower(clean_text((string)($_POST['email']??''),100));
    $password=(string)($_POST['password']??'');
    $stmt=$conn->prepare('SELECT * FROM sellers WHERE email=? LIMIT 1');
    $stmt->execute([$email]);
    $seller=$stmt->fetch();
    if($seller && password_matches($password,$seller['password'])){
        maybe_upgrade_password($conn,'sellers',$seller['id'],$password,$seller['password']);
        session_regenerate_id(true);
        $_SESSION['seller_id']=$seller['id'];
        flash('success','Welcome to the admin panel.');
        redirect('admin/dashboard.php');
    }
    $error='Invalid admin email or password.';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Login | Fashion Store</title><link rel="stylesheet" href="<?=e(app_url('css/admin_style.css'))?>"></head><body><main class="login-page"><form class="login-card" method="post"><img src="<?=e(app_url('assets/logo.svg'))?>" alt="Fashion"><h1>Admin login</h1><p class="muted">Manage products, stock, orders, and customers.</p><?=csrf_field()?><?php if($error):?><p class="badge canceled"><?=e($error)?></p><?php endif;?><div class="field"><label>Email</label><input type="email" name="email" required value="<?=e($_POST['email']??'')?>"></div><div class="field"><label>Password</label><input type="password" name="password" required></div><button class="button" type="submit" style="width:100%;margin-top:18px">Login</button><p style="text-align:center"><a href="<?=e(app_url('admin/register.php'))?>">Create seller account</a> · <a href="<?=e(app_url('home.php'))?>">Website</a></p></form></main><?php include __DIR__.'/../components/alert.php';?></body></html>
