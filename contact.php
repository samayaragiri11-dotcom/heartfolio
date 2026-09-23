<?php 
include 'assets/includes/navbar.php'; 
?>

<div class="container">
    <div class="section-title" style="margin-top: 60px;">
        <h2>Contact Us</h2>
        <p>Get in touch with Heartfolio</p>
    </div>
    <div class="form-container" style="max-width: 700px;">
        <form action="contact-process.php" method="POST">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" required>
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
    </div>
</div>

<?php include 'assets/includes/footer.php'; ?>
