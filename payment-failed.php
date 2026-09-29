<?php
require_once 'config/init.php';
$pageTitle = 'Payment Failed';

$bookingId = $_GET['booking'] ?? '';
$error = $_GET['error'] ?? '';
$amount = $_GET['amount'] ?? 0;

include 'includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <h1 data-aos="fade-up">Payment Failed</h1>
        <p data-aos="fade-up" data-aos-delay="100">Your payment could not be processed</p>
    </div>
</section>

<section class="booking-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="booking-form-wrapper text-center" data-aos="fade-up">
                    <div style="font-size: 5rem; color: #dc3545;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h3 class="fw-bold mt-3">Payment Failed!</h3>
                    <p class="text-muted">We were unable to process your payment. Please try again or choose a different payment method.</p>
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger mt-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Reason:</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Note:</strong> Your booking is still pending. No amount has been deducted from your account.
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($bookingId): ?>
                    <div class="mt-4">
                        <a href="payment.php?booking=<?php echo urlencode($bookingId); ?>&amount=<?php echo urlencode($amount); ?>" class="btn btn-primary-gradient me-2">
                            <i class="fas fa-redo me-2"></i>Try Again
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-3">
                        <a href="dashboard.php" class="btn btn-outline-primary">
                            <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                        </a>
                    </div>
                    
                    <hr class="my-4">
                    <h5 class="fw-bold">Need Help?</h5>
                    <p class="text-muted">Contact our support team for assistance with your payment.</p>
                    <a href="contact.php" class="btn btn-outline-primary">
                        <i class="fas fa-headset me-2"></i>Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

