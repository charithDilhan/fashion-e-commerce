<?php
require_once __DIR__ . '/../components/connect.php';
require_seller();
if (!is_system_admin($conn)) { flash('error', 'Access denied.'); redirect('admin/dashboard.php'); }

// HANDLE ADD SELLER FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_seller'])) {
    verify_csrf();
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    if ($name === '') $errors[] = 'Name is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm_password) $errors[] = 'Passwords do not match.';
    
    // Check if email already exists
    if (empty($errors)) {
        $check = $conn->prepare('SELECT id FROM sellers WHERE email = ? LIMIT 1');
        $check->execute([$email]);
        if ($check->fetch()) $errors[] = 'Email already registered.';
    }
    
    // Handle image upload
    $imageName = '';
    if (empty($errors) && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Invalid image format.';
        } else {
            $imageName = uniqid('seller_', true) . '.' . $ext;
            $uploadPath = __DIR__ . '/../uploads/' . $imageName;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $errors[] = 'Image upload failed.';
                $imageName = '';
            }
        }
    }
    
    if (empty($errors)) {
        $hashedPass = password_hash($password, PASSWORD_DEFAULT);
        $newId = bin2hex(random_bytes(12));
        
        $stmt = $conn->prepare('INSERT INTO sellers (id, name, email, password, image, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        if ($stmt->execute([$newId, $name, $email, $hashedPass, $imageName])) {
            flash('success', 'Seller added successfully!');
            header('Location: sellers_account.php');
            exit;
        } else {
            flash('error', 'Failed to add seller.');
        }
    } else {
        flash('error', implode(' ', $errors));
    }
}

// HANDLE DELETE SELLER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_seller'])) {
    verify_csrf();
    
    $sellerId = $_POST['seller_id'] ?? '';
    
    if ($sellerId !== '') {
        // Get seller info first (to delete image)
        $stmt = $conn->prepare('SELECT image FROM sellers WHERE id = ? LIMIT 1');
        $stmt->execute([$sellerId]);
        $seller = $stmt->fetch();
        
        if ($seller) {
            // Delete the seller's image file if exists
            if (!empty($seller['image'])) {
                $imagePath = __DIR__ . '/../uploads/' . $seller['image'];
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }
            
            // Delete seller from database
            $delStmt = $conn->prepare('DELETE FROM sellers WHERE id = ?');
            if ($delStmt->execute([$sellerId])) {
                flash('success', 'Seller deleted successfully!');
            } else {
                flash('error', 'Failed to delete seller.');
            }
        } else {
            flash('error', 'Seller not found.');
        }
    }
    
    header('Location: sellers_account.php');
    exit;
}

$sellers = $conn->query('SELECT s.*, (SELECT COUNT(*) FROM products p WHERE p.seller_id = s.id) AS product_count FROM sellers s ORDER BY s.created_at DESC')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sellers | Admin</title>
    <link rel="stylesheet" href="<?= e(app_url('css/admin_style.css')) ?>">
    <style>
        .add-seller-btn {
            background: #222;
            color: #fff;
            padding: 10px 22px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        .add-seller-btn:hover {
            background: #444;
            transform: translateY(-2px);
        }
        
        /* DELETE BUTTON */
        .delete-btn {
            background: #e74c3c;
            color: #fff;
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .delete-btn:hover {
            background: #c0392b;
            transform: translateY(-1px);
        }
        .delete-form {
            display: inline;
            margin: 0;
        }
        
        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .modal-overlay.active {
            display: flex;
            animation: fadeIn 0.3s ease;
        }
        .modal-box {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            animation: slideUp 0.4s ease;
        }
        .modal-box h2 {
            margin: 0 0 20px 0;
            font-size: 1.5rem;
            color: #222;
        }
        .modal-box .form-group {
            margin-bottom: 15px;
        }
        .modal-box label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        .modal-box input[type="text"],
        .modal-box input[type="email"],
        .modal-box input[type="password"],
        .modal-box input[type="file"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .modal-box input:focus {
            outline: none;
            border-color: #222;
        }
        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .btn-cancel {
            background: #eee;
            color: #333;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-submit {
            background: #222;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-submit:hover { background: #444; }
        .btn-cancel:hover { background: #ddd; }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/admin_header.php'; ?>

<main class="admin-main">
    <div class="admin-container">
        <div class="admin-page-heading">
            <div>
                <h1>Sellers</h1>
                <p>Registered seller accounts.</p>
            </div>
            <div>
                <button type="button" class="add-seller-btn" onclick="document.getElementById('addSellerModal').classList.add('active')">
                    + Add Seller
                </button>
            </div>
        </div>
        
        <section class="panel">
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Seller</th>
                            <th>Email</th>
                            <th>Products</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sellers as $item): ?>
                        <tr>
                            <td>
                                <div class="table-product">
                                    <img style="border-radius:50%;height:58px" src="<?= e(uploaded_image_url($item['image'], 'avatar')) ?>" alt="">
                                    <strong><?= e($item['name']) ?></strong>
                                </div>
                            </td>
                            <td><?= e($item['email']) ?></td>
                            <td><?= (int) $item['product_count'] ?></td>
                            <td><?= e(date('M d, Y', strtotime($item['created_at']))) ?></td>
                            <td>
                                <form method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete seller <?= e($item['name']) ?>? This action cannot be undone.');">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="delete_seller" value="1">
                                    <input type="hidden" name="seller_id" value="<?= e($item['id']) ?>">
                                    <button type="submit" class="delete-btn">🗑 Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<!-- ADD SELLER MODAL -->
<div class="modal-overlay" id="addSellerModal">
    <div class="modal-box">
        <h2>Add New Seller</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="add_seller" value="1">
            
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" required placeholder="Enter seller name">
            </div>
            
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" required placeholder="seller@example.com">
            </div>
            
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" required minlength="6" placeholder="Minimum 6 characters">
            </div>
            
            <div class="form-group">
                <label>Confirm Password *</label>
                <input type="password" name="confirm_password" required minlength="6" placeholder="Re-enter password">
            </div>
            
            <div class="form-group">
                <label>Profile Image (Optional)</label>
                <input type="file" name="image" accept="image/*">
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="document.getElementById('addSellerModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn-submit">Add Seller</button>
            </div>
        </form>
    </div>
</div>

<script>
// Close modal on outside click
document.getElementById('addSellerModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('active');
});
// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('addSellerModal').classList.remove('active');
    }
});
</script>

<?php include __DIR__ . '/../components/admin_footer.php'; include __DIR__ . '/../components/alert.php'; ?>
</body>
</html>