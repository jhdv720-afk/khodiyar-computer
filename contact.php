<?php
require_once 'config/init.php';
$pageTitle = 'Contact Us';
include 'includes/header.php';

// Handle contact form submission
$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'contact') {
    try {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message']);
        
        if (empty($name) || empty($email) || empty($message)) {
            throw new Exception('Please fill in all required fields.');
        }
        
        if (!validateEmail($email)) {
            throw new Exception('Please enter a valid email address.');
        }
        
        dbInsert(
            "INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)",
            [$name, $email, $phone, $subject, $message]
        );
        
        $success = true;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <nav class="breadcrumb-nav">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Contact Us</li>
            </ol>
        </nav>
        <h1 data-aos="fade-up">Contact Us</h1>
        <p data-aos="fade-up" data-aos-delay="100">Get in touch with us for any queries or support</p>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4" data-aos="fade-right">
                <div class="contact-info-card">
                    <h4 class="fw-bold mb-4">Get In Touch</h4>
                    
                    <div class="contact-info-item">
                        <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <h6>Our Address</h6>
                            <p><?php echo getSetting('company_address') ?? 'Rajkot, Gujarat, India'; ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <div class="icon"><i class="fas fa-phone"></i></div>
                        <div>
                            <h6>Phone Number</h6>
                            <p><?php echo getSetting('company_phone') ?? '+91 98765 43210'; ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <div class="icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <h6>Email Address</h6>
                            <p><?php echo getSetting('company_email') ?? 'info@khodiyarcomputer.com'; ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <div class="icon"><i class="fas fa-clock"></i></div>
                        <div>
                            <h6>Working Hours</h6>
                            <p><?php echo getSetting('working_hours') ?? 'Mon - Sat: 9:00 AM - 8:00 PM'; ?></p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h6 class="mb-3">Follow Us</h6>
                        <div class="d-flex gap-2">
                            <?php $fb = getSetting('facebook_url'); if($fb): ?>
                            <a href="<?php echo $fb; ?>" class="btn btn-outline-light btn-sm rounded-circle" style="width:40px;height:40px;"><i class="fab fa-facebook-f"></i></a>
                            <?php endif; ?>
                            <?php $ig = getSetting('instagram_url'); if($ig): ?>
                            <a href="<?php echo $ig; ?>" class="btn btn-outline-light btn-sm rounded-circle" style="width:40px;height:40px;"><i class="fab fa-instagram"></i></a>
                            <?php endif; ?>
                            <?php $wa = getSetting('whatsapp_number'); if($wa): ?>
                            <a href="https://wa.me/<?php echo $wa; ?>" class="btn btn-outline-light btn-sm rounded-circle" style="width:40px;height:40px;"><i class="fab fa-whatsapp"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8" data-aos="fade-left">
                <div class="contact-form p-4">
                    <h4 class="fw-bold mb-3">Send Us a Message</h4>
                    <p class="text-muted mb-4">We'll get back to you within 24 hours.</p>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>Your message has been sent successfully! We'll contact you soon.
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="contact">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control" maxlength="10">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subject</label>
                                <input type="text" name="subject" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message <span class="text-danger">*</span></label>
                                <textarea name="message" class="form-control" rows="5" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary-gradient btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

