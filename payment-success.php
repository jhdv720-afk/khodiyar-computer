<?php
require_once 'config/init.php';
$pageTitle = 'Payment Successful';

$paymentId = $_GET['payment'] ?? '';
$bookingId = $_GET['booking'] ?? '';

if (empty($paymentId)) {
    header('Location: index.php');
    exit;
}

// Get payment details
$payment = dbFetchOne(
    "SELECT p.*, b.service_id, s.title as service_name 
     FROM payments p 
     LEFT JOIN bookings b ON p.booking_id = b.booking_id 
     LEFT JOIN services s ON b.service_id = s.id 
     WHERE p.payment_id = ?",
    [$paymentId]
);

if (!$payment) {
    header('Location: index.php');
    exit;
}

include 'includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <h1 data-aos="fade-up">Payment Successful!</h1>
        <p data-aos="fade-up" data-aos-delay="100">Your payment has been processed successfully</p>
    </div>
</section>

<section class="booking-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="booking-form-wrapper text-center" data-aos="fade-up">
                    <div style="font-size: 5rem; color: #28a745;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="fw-bold mt-3">Payment Successful!</h3>
                    <p class="text-muted">Your payment has been processed successfully. Thank you for choosing Khodiyar Computer!</p>
                    
                    <div class="bg-light p-4 rounded-3 mt-4 text-start">
                        <h5 class="fw-bold mb-3"><i class="fas fa-receipt text-primary me-2"></i>Payment Receipt</h5>
                        <table class="table table-sm">
                            <tr>
                                <td class="text-muted">Payment ID:</td>
                                <td class="fw-bold text-end"><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Transaction Ref:</td>
                                <td class="fw-bold text-end"><?php echo htmlspecialchars($payment['transaction_ref']); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Booking ID:</td>
                                <td class="fw-bold text-end"><?php echo htmlspecialchars($payment['booking_id']); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Service:</td>
                                <td class="fw-bold text-end"><?php echo htmlspecialchars($payment['service_name']); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Amount Paid:</td>
                                <td class="fw-bold text-end text-primary fs-5">₹<?php echo number_format($payment['amount']); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Payment Method:</td>
                                <td class="fw-bold text-end"><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Date:</td>
                                <td class="fw-bold text-end"><?php echo date('d M Y h:i A', strtotime($payment['payment_date'])); ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        <p class="text-muted">We will contact you shortly to confirm your service appointment.</p>
                        <a href="dashboard.php" class="btn btn-primary-gradient me-2">
                            <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                        </a>
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="fas fa-home me-2"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

