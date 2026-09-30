<?php
require_once __DIR__ . '/../components/connect.php';
if($seller_id!=='') redirect('admin/dashboard.php');
$errors=[];
if(is_post()){
    $name=clean_text((string)($_POST['name']??''),50);
    $email=strtolower(clean_text((string)($_POST['email']??''),100));
    $password=(string)($_POST['password']??'');
    $confirm=(string)($_POST['confirm_password']??'');
    if(strlen($name)<2)$errors[]='Name must contain at least 2 characters.';
    if(!filter_var($email,FILTER_VALIDATE_EMAIL))$errors[]='Enter a valid email address.';
    if(strlen($password)<8)$errors[]='Password must contain at least 8 characters.';
    if($password!==$confirm)$errors[]='Passwords do not match.';
    $check=$conn->prepare('SELECT id FROM sellers WHERE email=? LIMIT 1');$check->execute([$email]);if($check->fetch())$errors[]='A seller already uses this email.';
    if(!$errors){try{$image=upload_image('image');$id=unique_id();$stmt=$conn->prepare('INSERT INTO sellers(id,name,email,password,image) VALUES(?,?,?,?,?)');$stmt->execute([$id,$name,$email,password_hash($password,PASSWORD_DEFAULT),$image]);session_regenerate_id(true);$_SESSION['seller_id']=$id;flash('success','Seller account created.');redirect('admin/dashboard.php');} catch (Throwable $ex) { $errors[] = friendly_exception($ex, 'The seller account could not be created. Please try again.'); }}
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Seller Registration | Fashion Store</title><link rel="stylesheet" href="<?=e(app_url('css/admin_style.css'))?>"></head><body><main class="login-page"><form class="login-card" method="post" enctype="multipart/form-data"><img src="<?=e(app_url('assets/logo.svg'))?>" alt="Fashion"><h1>Create seller account</h1><?=csrf_field()?><?php foreach($errors as $item):?><p class="badge canceled"><?=e($item)?></p><?php endforeach;?><div class="field"><label>Name</label><input name="name" required value="<?=e($_POST['name']??'')?>"></div><div class="field"><label>Email</label><input type="email" name="email" required value="<?=e($_POST['email']??'')?>"></div><div class="field"><label>Password</label><input type="password" name="password" minlength="8" required></div><div class="field"><label>Confirm password</label><input type="password" name="confirm_password" minlength="8" required></div><div class="field"><label>Profile image (optional)</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/avif"></div><button class="button" type="submit" style="width:100%;margin-top:18px">Create account</button><p style="text-align:center"><a href="<?=e(app_url('admin/login.php'))?>">Back to login</a></p></form></main></body></html>
