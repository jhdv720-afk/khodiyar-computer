<?php
require_once 'config/init.php';
$pageTitle = 'Book a Service';
include 'includes/header.php';

$services = getActiveServices();
$selectedService = $_GET['service'] ?? '';

// Handle form submission
$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $service_id = (int)$_POST['service_id'];
        $customer_name = sanitize($_POST['customer_name']);
        $customer_email = sanitize($_POST['customer_email']);
        $customer_phone = sanitize($_POST['customer_phone']);
        $customer_address = sanitize($_POST['customer_address'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $preferred_date = $_POST['preferred_date'];
        $preferred_time = $_POST['preferred_time'] ?? null;
        $estimated_budget = !empty($_POST['estimated_budget']) ? (float)$_POST['estimated_budget'] : null;
        $payment_option = $_POST['payment_option'] ?? 'pay_at_service'; // 'pay_online' or 'pay_at_service'
        
        // Validation
        if (empty($service_id) || empty($customer_name) || empty($customer_email) || empty($customer_phone) || empty($preferred_date)) {
            throw new Exception('Please fill in all required fields.');
        }
        
        if (!validateEmail($customer_email)) {
            throw new Exception('Please enter a valid email address.');
        }
        
        if (!preg_match('/^[0-9]{10}$/', $customer_phone)) {
            throw new Exception('Please enter a valid 10-digit phone number.');
        }
        
        // Check date is not in past
        $today = date('Y-m-d');
        if ($preferred_date < $today) {
            throw new Exception('Please select a future date.');
        }
        
        // Verify service exists
        $service = getServiceById($service_id);
        if (!$service) {
            throw new Exception('Invalid service selected.');
        }
        
        // Generate unique booking ID
        $booking_id = generateBookingId();
        
        // Insert booking
        $userId = isUserLoggedIn() ? $_SESSION['user_id'] : null;
        $paymentRequired = ($payment_option === 'pay_online' && $estimated_budget > 0) ? 1 : 0;
        
        $sql = "INSERT INTO bookings (booking_id, user_id, service_id, customer_name, customer_email, customer_phone, customer_address, description, preferred_date, preferred_time, estimated_budget, payment_required, payment_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        dbInsert($sql, [
            $booking_id, $userId, $service_id, $customer_name, $customer_email,
            $customer_phone, $customer_address, $description, $preferred_date, $preferred_time,
            $estimated_budget, $paymentRequired, $paymentRequired ? 'pending' : 'none'
        ]);
        
        // If user wants to pay online and amount > 0, redirect to payment
        if ($paymentRequired) {
            setFlashMessage('info', 'Your booking has been created. Please complete the payment to confirm.');
            header('Location: payment.php?booking=' . urlencode($booking_id) . '&amount=' . urlencode($estimated_budget));
            exit;
        }
        
        $success = true;
        $bookingRef = $booking_id;
        
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
                <li class="breadcrumb-item active">Book a Service</li>
            </ol>
        </nav>
        <h1 data-aos="fade-up">Book a Service</h1>
        <p data-aos="fade-up" data-aos-delay="100">Schedule your service appointment with us</p>
    </div>
</section>

<!-- Booking Section -->
<section class="booking-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($success): ?>
                <div class="booking-form-wrapper text-center" data-aos="fade-up">
                    <div class="mb-4">
                        <div style="font-size: 4rem; color: #28a745;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="fw-bold mt-3">Booking Successful!</h3>
                        <p class="text-muted">Your service request has been submitted successfully.</p>
                        <div class="bg-light p-4 rounded-3 mt-4">
                            <p class="mb-1"><strong>Booking Reference:</strong></p>
                            <h4 class="text-primary fw-bold"><?php echo $bookingRef; ?></h4>
                            <p class="text-muted small mt-2">Please save this reference number for future correspondence.</p>
                        </div>
                        <p class="mt-3">We will contact you shortly to confirm your appointment.</p>
                        <div class="mt-4">
                            <a href="index.php" class="btn btn-primary-gradient me-2">
                                <i class="fas fa-home me-2"></i>Back to Home
                            </a>
                            <a href="book-service.php" class="btn btn-outline-primary">
                                <i class="fas fa-plus me-2"></i>Book Another
                            </a>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="booking-form-wrapper" data-aos="fade-up">
                    <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <h4 class="fw-bold mb-3"><i class="fas fa-calendar-check text-primary me-2"></i>Book Your Service</h4>
                    <p class="text-muted mb-4">Fill in the details below and we'll get back to you.</p>
                    
                    <form method="POST" action="" id="bookingForm">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Select Service <span class="text-danger">*</span></label>
                                <select name="service_id" class="form-select" required>
                                    <option value="">-- Choose a Service --</option>
                                    <?php foreach ($services as $svc): ?>
                                    <option value="<?php echo $svc['id']; ?>" <?php echo ($selectedService === $svc['slug']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($svc['title']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" class="form-control" value="<?php echo isUserLoggedIn() ? htmlspecialchars($_SESSION['user_name']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="customer_email" class="form-control" value="<?php echo isUserLoggedIn() ? htmlspecialchars($_SESSION['user_email']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="tel" name="customer_phone" class="form-control" placeholder="10-digit number" maxlength="10" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Preferred Date <span class="text-danger">*</span></label>
                                <input type="date" name="preferred_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Preferred Time</label>
                                <input type="time" name="preferred_time" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Address</label>
                                <input type="text" name="customer_address" class="form-control" placeholder="Your address">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estimated Budget (₹) <span class="text-muted small">(Optional)</span></label>
                                <input type="number" name="estimated_budget" class="form-control" min="1" step="0.01" placeholder="e.g. 5000">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Payment Option <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="form-check payment-option-card border rounded-3 p-3">
                                            <input class="form-check-input" type="radio" name="payment_option" id="payAtService" value="pay_at_service" checked>
                                            <label class="form-check-label fw-bold" for="payAtService">
                                                <i class="fas fa-money-bill-wave text-warning me-2"></i>Pay at Service
                                            </label>
                                            <div class="text-muted small mt-1">Pay when our team arrives at your location.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check payment-option-card border rounded-3 p-3">
                                            <input class="form-check-input" type="radio" name="payment_option" id="payOnline" value="pay_online">
                                            <label class="form-check-label fw-bold" for="payOnline">
                                                <i class="fas fa-credit-card text-primary me-2"></i>Pay Online Now
                                            </label>
                                            <div class="text-muted small mt-1">Secure payment via UPI, Cards, Net Banking.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description / Requirements</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Tell us about your requirements..."></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary-gradient w-100 py-3">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Booking Request
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

