<?php
require_once __DIR__ . '/components/connect.php';
$user = current_user($conn);
$errors = [];

if (is_post()) {
    $name = clean_text((string) ($_POST['name'] ?? ''), 50);
    $email = strtolower(clean_text((string) ($_POST['email'] ?? ''), 100));
    $subject = clean_text((string) ($_POST['subject'] ?? ''), 100);
    $message = trim((string) ($_POST['message'] ?? ''));

    if (strlen($name) < 2) $errors[] = 'Enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($subject) < 3) $errors[] = 'Enter a subject.';
    if (strlen($message) < 10) $errors[] = 'Message must contain at least 10 characters.';
    if (strlen($message) > 2000) $errors[] = 'Message is too long.';

    if (!$errors) {
        try {
            // Guests are allowed to contact the store. Only attach a user_id when
            // the authenticated database record still exists.
            $messageUserId = $user ? (string) $user['id'] : null;
            $stmt = $conn->prepare('INSERT INTO message(id,user_id,name,email,subject,message) VALUES(?,?,?,?,?,?)');
            $stmt->execute([unique_id(), $messageUserId, $name, $email, $subject, $message]);
            flash('success', 'Your message was sent successfully.');
            redirect('contact.php');
        } catch (PDOException $exception) {
            // A user can be deleted in another tab between page load and submit.
            // Retry as a guest instead of exposing a foreign-key fatal error.
            if ((string) $exception->getCode() === '23000' && $user) {
                unset($_SESSION['user_id']);
                $GLOBALS['user_id'] = '';
                try {
                    $stmt = $conn->prepare('INSERT INTO message(id,user_id,name,email,subject,message) VALUES(?,?,?,?,?,?)');
                    $stmt->execute([unique_id(), null, $name, $email, $subject, $message]);
                    flash('success', 'Your message was sent successfully.');
                    redirect('contact.php');
                } catch (Throwable $retryException) {
                    $errors[] = friendly_exception($retryException, 'Your message could not be sent. Please try again.');
                }
            } else {
                $errors[] = friendly_exception($exception, 'Your message could not be sent. Please try again.');
            }
        } catch (Throwable $exception) {
            $errors[] = friendly_exception($exception, 'Your message could not be sent. Please try again.');
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Contact | Fashion Store</title><link rel="stylesheet" href="<?= e(app_url('css/user_style.css')) ?>?v=<?= filemtime(app_root_path().'/css/user_style.css') ?>"></head><body>
<?php include __DIR__ . '/components/user_header.php'; ?>
<section class="page-hero" style="--hero-image:url('https://images.pexels.com/photos/6347547/pexels-photo-6347547.jpeg?auto=compress&cs=tinysrgb&w=1600');"><div class="container"><div class="hero-copy"><span class="eyebrow">Get in touch</span><h1>Contact our fashion team</h1><p>Send a message to the store administration for support, orders, or general inquiries.</p></div></div></section>
<section class="section"><div class="container contact-grid"><form class="form-card" method="post"><h2>How can we help?</h2><p class="muted">We usually reply as soon as possible through the admin panel message system.</p><?= csrf_field() ?><?php foreach ($errors as $item): ?><p class="badge canceled"><?= e($item) ?></p><?php endforeach; ?><div class="form-grid"><div class="field"><label>Name</label><input name="name" required maxlength="50" value="<?= e($_POST['name'] ?? $user['name'] ?? '') ?>"></div><div class="field"><label>Email</label><input type="email" name="email" required maxlength="100" value="<?= e($_POST['email'] ?? $user['email'] ?? '') ?>"></div><div class="field full"><label>Subject</label><input name="subject" required maxlength="100" value="<?= e($_POST['subject'] ?? '') ?>"></div><div class="field full"><label>Message</label><textarea name="message" required minlength="10" maxlength="2000"><?= e($_POST['message'] ?? '') ?></textarea></div></div><button class="button" type="submit" style="margin-top:18px">Send message</button></form><aside class="info-card"><h3>Store communication</h3><ul><li><strong>Support</strong><br><span class="muted">Use this page to contact store management directly.</span></li><li><strong>Admin review</strong><br><span class="muted">Messages appear in the admin message panel for quick handling.</span></li><li><strong>Editable image links</strong><br><span class="muted">Background image URLs are added in code, so you can replace them anytime.</span></li></ul></aside></div></section>
<?php include __DIR__ . '/components/user_footer.php'; include __DIR__ . '/components/alert.php'; ?>
</body></html>

