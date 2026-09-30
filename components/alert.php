<?php
$allFlashes = pull_flashes();
foreach (($success_msg ?? []) as $message) {
    $allFlashes[] = ['type' => 'success', 'message' => $message];
}
foreach (($warning_msg ?? []) as $message) {
    $allFlashes[] = ['type' => 'warning', 'message' => $message];
}
foreach (($error_msg ?? []) as $message) {
    $allFlashes[] = ['type' => 'error', 'message' => $message];
}
foreach (($info_msg ?? []) as $message) {
    $allFlashes[] = ['type' => 'info', 'message' => $message];
}
?>
<?php if ($allFlashes): ?>
<div class="toast-stack" aria-live="polite">
    <?php foreach ($allFlashes as $flash): ?>
        <div class="toast toast-<?= e($flash['type'] ?? 'info') ?>">
            <span><?= e($flash['message'] ?? '') ?></span>
            <button type="button" aria-label="Close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
