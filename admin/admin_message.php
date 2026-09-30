<?php
require_once __DIR__ . '/../components/connect.php';
require_seller();
if (!is_system_admin($conn)) {
    flash('error', 'Access denied.');
    redirect('admin/dashboard.php');
}
if (is_post() && isset($_POST['delete_message'])) {
    $id = clean_text((string) ($_POST['message_id'] ?? ''), 20);
    $stmt = $conn->prepare('DELETE FROM message WHERE id = ?');
    $stmt->execute([$id]);
    flash('success', 'Message deleted.');
    redirect('admin/admin_message.php');
}
$messages = $conn->query('SELECT * FROM message ORDER BY created_at DESC')->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Messages | Admin</title><link rel="stylesheet" href="<?= e(app_url('css/admin_style.css')) ?>"></head><body>
<?php include __DIR__ . '/../components/admin_header.php'; ?>
<main class="admin-main"><div class="admin-container"><div class="admin-page-heading"><div><h1>Messages</h1><p>Customer enquiries sent through the contact page.</p></div></div><section class="panel"><?php if ($messages): ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Sender</th><th>Subject</th><th>Message</th><th>Date</th><th></th></tr></thead><tbody><?php foreach ($messages as $message): ?><tr><td><strong><?= e($message['name']) ?></strong><small style="display:block;color:var(--muted)"><?= e($message['email']) ?></small></td><td><?= e($message['subject']) ?></td><td style="max-width:420px;white-space:normal"><?= nl2br(e($message['message'])) ?></td><td><?= e(date('M d, Y H:i', strtotime($message['created_at']))) ?></td><td><form method="post" onsubmit="return confirm('Delete this message?')"><?= csrf_field() ?><input type="hidden" name="message_id" value="<?= e($message['id']) ?>"><button class="button danger small" type="submit" name="delete_message">Delete</button></form></td></tr><?php endforeach; ?></tbody></table></div><?php else: ?><div class="empty-state">No messages yet.</div><?php endif; ?></section></div></main>
<?php include __DIR__ . '/../components/admin_footer.php'; include __DIR__ . '/../components/alert.php'; ?>
</body></html>
