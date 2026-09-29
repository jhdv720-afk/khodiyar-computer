<?php
require_once 'config/init.php';
$pageTitle = '404 - Page Not Found';
include 'includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center" data-aos="fade-up">
                <div style="font-size: 8rem; font-weight: 800; background: linear-gradient(135deg, #6C3CE1, #00D2FF); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                    404
                </div>
                <h2 class="fw-bold mb-3">Page Not Found</h2>
                <p class="text-muted mb-4">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
                <div>
                    <a href="index.php" class="btn btn-primary-gradient btn-lg me-2">
                        <i class="fas fa-home me-2"></i>Go Home
                    </a>
                    <a href="contact.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-envelope me-2"></i>Contact Us
                    </a>
                </div>
        </div>
</section>

<?php include 'includes/footer.php'; ?>
