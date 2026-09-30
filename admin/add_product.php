<?php
require_once __DIR__ . '/../components/connect.php';
require_seller();
$errors=[];
if(is_post() && isset($_POST['save_product'])){
    $name=clean_text((string)($_POST['name']??''),80);
    $price=(float)($_POST['price']??0);
    $stock=(int)($_POST['stock']??0);
    $category=clean_text((string)($_POST['category']??''),100);
    $detail=trim((string)($_POST['product_detail']??''));
    $status=in_array($_POST['status']??'active',['active','inactive'],true)?$_POST['status']:'active';
    if(strlen($name)<2)$errors[]='Product name is required.';
    if($price<=0)$errors[]='Price must be greater than zero.';
    if($stock<0)$errors[]='Stock cannot be negative.';
    if (!in_array($category, product_categories(), true)) $errors[] = 'Select a valid category.';
    if (strlen($detail) < 5) $errors[] = 'Add a clear product description.';
    if (strlen($detail) > 5000) $errors[] = 'Product description is too long.';
    if(!$errors){
        try{
            $one=upload_image('thumb_one');$two=upload_image('thumb_two');$three=upload_image('thumb_three');
            $stmt=$conn->prepare('INSERT INTO products(id,seller_id,name,price,category,thumb_one,thumb_two,thumb_three,stock,product_detail,status) VALUES(?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([unique_id(),$seller_id,$name,$price,$category,$one,$two,$three,$stock,$detail,$status]);
            flash('success','Product added successfully.');redirect('admin/view_product.php');
        } catch (Throwable $ex) { $errors[] = friendly_exception($ex, 'The product could not be added. Please try again.'); }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Add Product | Admin</title><link rel="stylesheet" href="<?=e(app_url('css/admin_style.css'))?>"></head><body><?php include __DIR__.'/../components/admin_header.php';?><main class="admin-main"><div class="admin-container"><div class="admin-page-heading"><div><h1>Add product</h1><p>Images are optional; a lightweight placeholder appears when none is uploaded.</p></div></div><form class="panel" method="post" enctype="multipart/form-data"><?=csrf_field()?><?php foreach($errors as $item):?><p class="badge canceled"><?=e($item)?></p><?php endforeach;?><div class="form-grid"><div class="field"><label>Product name</label><input name="name" required maxlength="80" value="<?=e($_POST['name']??'')?>"></div><div class="field"><label>Category</label><select name="category" required><option value="">Select category</option><?php foreach(product_categories() as $cat):?><option value="<?=e($cat)?>" <?=($_POST['category']??'')===$cat?'selected':''?>><?=e($cat)?></option><?php endforeach;?></select></div><div class="field"><label>Price (LKR)</label><input type="number" name="price" min="0.01" step="0.01" required value="<?=e($_POST['price']??'')?>"></div><div class="field"><label>Stock quantity</label><input type="number" name="stock" min="0" required value="<?=e($_POST['stock']??'0')?>"></div><div class="field"><label>Status</label><select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div><div class="field full"><label>Description <small style="color:var(--muted); font-weight:normal; display:inline-block; margin-left:6px;">(Tip: Add sizes like <code>Size: [04] [05] [06]</code> or <code>Size: S, M, L</code> for interactive size buttons)</small></label><textarea name="product_detail" required><?=e($_POST['product_detail']??'')?></textarea></div><div class="field"><label>Main image (optional)</label><input type="file" name="thumb_one" accept="image/jpeg,image/png,image/webp,image/avif"></div><div class="field"><label>Second image (optional)</label><input type="file" name="thumb_two" accept="image/jpeg,image/png,image/webp,image/avif"></div><div class="field"><label>Third image (optional)</label><input type="file" name="thumb_three" accept="image/jpeg,image/png,image/webp,image/avif"></div></div><div style="margin-top:20px;display:flex;gap:10px"><button class="button" type="submit" name="save_product">Save product</button><a class="button secondary" href="<?=e(app_url('admin/view_product.php'))?>">Cancel</a></div></form></div></main><?php include __DIR__.'/../components/admin_footer.php';include __DIR__.'/../components/alert.php';?></body></html>
