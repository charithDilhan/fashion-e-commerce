<?php
$seller = current_seller($conn);
$isSystemAdmin = is_system_admin($conn);
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
?>
<header class="admin-topbar">
    <button type="button" class="admin-menu-toggle" data-admin-menu-toggle aria-label="Open admin menu"><svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
    <a class="admin-brand" href="<?= e(app_url('admin/dashboard.php')) ?>"><img src="<?= e(app_url('assets/logo.svg')) ?>" alt="Fashion Store"></a>
    <div class="admin-user">
        <img src="<?= e(uploaded_image_url($seller['image'] ?? '', 'avatar')) ?>" alt="Admin">
        <div><strong><?= e($seller['name'] ?? 'Admin') ?></strong><small><?= $isSystemAdmin ? 'System / Super Admin' : 'Seller Account' ?></small></div>
    </div>
</header>
<aside class="admin-sidebar" data-admin-sidebar>
    <nav>
        <a class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="<?= e(app_url('admin/dashboard.php')) ?>">Dashboard</a>
        <a class="<?= $currentPage === 'add_product.php' ? 'active' : '' ?>" href="<?= e(app_url('admin/add_product.php')) ?>">Add product</a>
        <a class="<?= in_array($currentPage, ['view_product.php','read_product.php','edit_product.php','edite_product.php'], true) ? 'active' : '' ?>" href="<?= e(app_url('admin/view_product.php')) ?>">Products</a>
        <a class="<?= $currentPage === 'admin_orders.php' ? 'active' : '' ?>" href="<?= e(app_url('admin/admin_orders.php')) ?>">Orders</a>
        <?php if ($isSystemAdmin): ?>
            <a class="<?= $currentPage === 'admin_message.php' ? 'active' : '' ?>" href="<?= e(app_url('admin/admin_message.php')) ?>">Messages</a>
            <a class="<?= $currentPage === 'user_account.php' ? 'active' : '' ?>" href="<?= e(app_url('admin/user_account.php')) ?>">Customers</a>
            <a class="<?= $currentPage === 'sellers_account.php' ? 'active' : '' ?>" href="<?= e(app_url('admin/sellers_account.php')) ?>">Sellers</a>
        <?php endif; ?>
        <a class="<?= in_array($currentPage, ['profile.php','update.php'], true) ? 'active' : '' ?>" href="<?= e(app_url('admin/profile.php')) ?>">Profile</a>
        <a href="<?= e(app_url('home.php')) ?>" target="_blank">View website</a>
        <a class="<?= $currentPage === 'reports.php' ? 'active' : '' ?>" href="<?= e(app_url('admin/reports.php')) ?>">Reports</a>
    </nav>
    <form action="<?= e(app_url('components/admin_logout.php')) ?>" method="post">
        <?= csrf_field() ?>
        <button type="submit" class="sidebar-logout">Logout</button>
    </form>
</aside>
