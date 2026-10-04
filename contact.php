<?php
require_once 'assets/includes/bootstrap.php';

$user = current_user();
$form = ['name' => $user['fullname'] ?? '', 'email' => $user['email'] ?? '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($form as $k => $_) {
        $form[$k] = trim($_POST[$k] ?? '');
    }
    // Hidden field that people never fill in, but spam bots do
    $isBot = trim($_POST['website'] ?? '') !== '';

    $error = null;
    if ($form['name'] === '' || mb_strlen($form['name']) > 100) {
        $error = 'Enter your name.';
    } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address so we can reply.';
    } elseif ($form['subject'] === '' || mb_strlen($form['subject']) > 150) {
        $error = 'Add a short subject.';
    } elseif ($form['message'] === '' || mb_strlen($form['message']) > 3000) {
        $error = 'Write your message (up to 3000 characters).';
    }

    if ($error) {
        flash('error', $error);
    } else {
        if (!$isBot) {
            db_exec("INSERT INTO messages (type, user_id, name, email, subject, message) VALUES ('contact', ?, ?, ?, ?, ?)",
                [$user ? (int)$user['id'] : null, $form['name'], $form['email'], $form['subject'], $form['message']]);
        }
        flash('success', 'Thanks, ' . $form['name'] . '! Your message is sent. We usually reply within a day.');
        redirect('contact.php');
    }
}

$pageTitle = 'Contact us';
$active = 'contact';
include 'assets/includes/navbar.php';
?>

<div class="wrap page">
    <div class="contact-grid">
        <div>
            <h1>Contact us</h1>
            <p class="muted" style="font-size: 1.05rem;">Questions about an order, a custom idea or a bulk gift? Send us a message and we'll get back to you.</p>
            <ul class="contact-points">
                <li><i class="fa-regular fa-clock" aria-hidden="true"></i><div><strong>Reply time</strong><span class="muted">Usually within one working day</span></div></li>
                <li><i class="fa-solid fa-receipt" aria-hidden="true"></i><div><strong>About an order?</strong><span class="muted">Include your order number, e.g. HF<?php echo date('ymd'); ?>-1234</span></div></li>
                <li><i class="fa-solid fa-location-dot" aria-hidden="true"></i><div><strong>Delivery</strong><span class="muted">Across Nepal, flat <?php echo price(SHIPPING_FEE); ?></span></div></li>
            </ul>
        </div>

        <form class="card" method="post">
            <?php echo flash_render(); ?>
            <?php echo csrf_field(); ?>
            <div style="position: absolute; left: -9999px;" aria-hidden="true">
                <label for="website">Leave this empty</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo e($form['name']); ?>" required maxlength="100">
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo e($form['email']); ?>" required>
                </div>
            </div>
            <div class="field">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" value="<?php echo e($form['subject']); ?>" required maxlength="150">
            </div>
            <div class="field">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="6" required maxlength="3000"><?php echo e($form['message']); ?></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Send message</button>
        </form>
    </div>
</div>

<?php include 'assets/includes/footer.php'; ?>
