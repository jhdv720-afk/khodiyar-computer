<?php
require_once 'config/init.php';
$pageTitle = 'Make Payment';

$bookingId = $_GET['booking'] ?? '';
$amount = $_GET['amount'] ?? 0;

if (empty($bookingId) || $amount <= 0) {
    header('Location: index.php');
    exit;
}

// Get booking details
$booking = dbFetchOne(
    "SELECT b.*, s.title as service_name FROM bookings b LEFT JOIN services s ON b.service_id = s.id WHERE b.booking_id = ?",
    [$bookingId]
);
if (!$booking) {
    header('Location: index.php');
    exit;
}

$amount = (float)$amount;
$amountInPaise = (int)round($amount * 100);

// Determine if we should use the real Razorpay gateway
$razorpayConfigured = isRazorpayConfigured();
$razorpayEnabled = isRazorpayEnabled();
$useRazorpay = $razorpayConfigured && $razorpayEnabled;

$error = null;
$razorpayOrder = null;
$rzpOrderError = null;

// If using Razorpay, create an order server-side
if ($useRazorpay) {
    $razorpaySettings = getRazorpaySettings();
    
    // Create Razorpay order
    $receipt = 'rcpt_' . $bookingId;
    $orderResult = createRazorpayOrder($amountInPaise, $receipt, [
        'booking_id' => $bookingId,
        'customer_name' => $booking['customer_name'],
        'customer_email' => $booking['customer_email']
    ]);
    
    if ($orderResult['success']) {
        $razorpayOrder = $orderResult['order'];
        
        // Store order info in session for verification
        $_SESSION['razorpay_order_id'] = $razorpayOrder['id'];
        $_SESSION['razorpay_amount'] = $amountInPaise;
        $_SESSION['razorpay_booking_id'] = $bookingId;
    } else {
        $rzpOrderError = $orderResult['message'];
        // Fall back to demo mode if order creation fails
        $useRazorpay = false;
    }
}

// Demo/fallback payment processing (when Razorpay not configured or order creation fails)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$useRazorpay) {
    try {
        $paymentMethod = $_POST['payment_method'] ?? '';
        
        if (empty($paymentMethod)) {
            throw new Exception('Please select a payment method.');
        }
        
        $paymentId = generatePaymentId();
        
        // Simulate payment processing
        $paymentStatus = 'paid';
        $transactionRef = 'TXN' . strtoupper(uniqid());
        
        $cardLastFour = null;
        $cardType = null;
        $bankName = null;
        $upiId = null;
        
        if ($paymentMethod === 'card') {
            $cardNumber = $_POST['card_number'] ?? '';
            $cardLastFour = substr(preg_replace('/[^0-9]/', '', $cardNumber), -4);
            $cardType = $_POST['card_type'] ?? 'Visa';
            
            if (empty($cardNumber) || strlen(preg_replace('/[^0-9]/', '', $cardNumber)) < 16) {
                throw new Exception('Please enter a valid 16-digit card number.');
            }
        } elseif ($paymentMethod === 'net_banking') {
            $bankName = $_POST['bank_name'] ?? '';
            if (empty($bankName)) {
                throw new Exception('Please select your bank.');
            }
        } elseif ($paymentMethod === 'upi') {
            $upiId = $_POST['upi_id'] ?? '';
            if (empty($upiId)) {
                throw new Exception('Please enter your UPI ID.');
            }
        }
        
        // Insert payment record
        $userId = isUserLoggedIn() ? $_SESSION['user_id'] : null;
        
        dbInsert(
            "INSERT INTO payments (payment_id, booking_id, user_id, customer_name, customer_email, customer_phone, 
             amount, payment_method, card_last_four, card_type, bank_name, upi_id, transaction_ref, payment_status, payment_date) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'paid', NOW())",
            [
                $paymentId, $bookingId, $userId, $booking['customer_name'], $booking['customer_email'],
                $booking['customer_phone'], $amount, $paymentMethod, $cardLastFour, $cardType,
                $bankName, $upiId, $transactionRef
            ]
        );
        
        // Update booking payment status
        dbQuery(
            "UPDATE bookings SET payment_status = 'paid', payment_required = 0 WHERE booking_id = ?",
            [$bookingId]
        );
        
        // Send confirmation email (graceful fallback if PHPMailer missing)
        sendBookingConfirmation($booking);
        
        header('Location: payment-success.php?payment=' . $paymentId . '&booking=' . $bookingId);
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        
        // Log failed payment attempt
        if (isset($paymentId)) {
            dbInsert(
                "INSERT INTO payments (payment_id, booking_id, customer_name, customer_email, customer_phone, 
                 amount, payment_method, payment_status, payment_response) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'failed', ?)",
                [$paymentId, $bookingId, $booking['customer_name'], $booking['customer_email'],
                 $booking['customer_phone'], $amount, $paymentMethod ?? '', $e->getMessage()]
            );
        }
    }
}

include 'includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <nav class="breadcrumb-nav">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="book-service.php">Book Service</a></li>
                <li class="breadcrumb-item active">Payment</li>
            </ol>
        </nav>
        <h1 data-aos="fade-up">Complete Payment</h1>
        <p data-aos="fade-up" data-aos-delay="100">Secure payment processing for your booking</p>
    </div>
</section>

<section class="booking-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="booking-form-wrapper" data-aos="fade-up">
                    <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($rzpOrderError): ?>
                    <div class="alert alert-warning alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Razorpay order creation failed:</strong> <?php echo htmlspecialchars($rzpOrderError); ?>
                        <br><small>Showing demo payment options instead. Configure Razorpay keys in admin settings.</small>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Booking Summary -->
                    <div class="bg-light p-4 rounded-3 mb-4">
                        <h5 class="fw-bold mb-3"><i class="fas fa-receipt text-primary me-2"></i>Booking Summary</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Booking ID:</strong> <?php echo htmlspecialchars($booking['booking_id']); ?></p>
                                <p class="mb-1"><strong>Service:</strong> <?php echo htmlspecialchars($booking['service_name']); ?></p>
                                <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($booking['customer_name']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Date:</strong> <?php echo date('d M Y', strtotime($booking['preferred_date'])); ?></p>
                                <p class="mb-1"><strong>Time:</strong> <?php echo $booking['preferred_time'] ? date('h:i A', strtotime($booking['preferred_time'])) : 'Flexible'; ?></p>
                                <p class="mb-1"><strong>Amount:</strong> <span class="text-primary fw-bold fs-5">₹<?php echo number_format($amount); ?></span></p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($useRazorpay && $razorpayOrder): ?>
                    <!-- Real Razorpay Checkout -->
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <img src="https://razorpay.com/assets/razorpay-glyph.svg" alt="Razorpay" style="height: 50px;" onerror="this.style.display='none'">
                        </div>
                        <h4 class="fw-bold mb-2"><i class="fas fa-lock text-primary me-2"></i>Secure Payment via Razorpay</h4>
                        <p class="text-muted mb-4">
                            Pay securely using UPI, Cards, Net Banking, Wallets & more.<br>
                            <span class="badge bg-success"><i class="fas fa-shield-alt me-1"></i>256-bit SSL Secured</span>
                        </p>
                        
                        <div class="payment-methods mb-4">
                            <div class="row g-2 justify-content-center">
                                <div class="col-auto">
                                    <div class="payment-badge p-2 px-3 rounded-3 border"><i class="fas fa-mobile-alt text-success me-1"></i> UPI</div>
                                </div>
                                <div class="col-auto">
                                    <div class="payment-badge p-2 px-3 rounded-3 border"><i class="fas fa-credit-card text-primary me-1"></i> Cards</div>
                                </div>
                                <div class="col-auto">
                                    <div class="payment-badge p-2 px-3 rounded-3 border"><i class="fas fa-university text-info me-1"></i> Net Banking</div>
                                </div>
                                <div class="col-auto">
                                    <div class="payment-badge p-2 px-3 rounded-3 border"><i class="fas fa-wallet text-warning me-1"></i> Wallets</div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-primary-gradient btn-lg py-3 px-5" id="rzpPayButton" onclick="openRazorpayCheckout()">
                            <i class="fas fa-lock me-2"></i>Pay ₹<?php echo number_format($amount); ?> Now
                        </button>
                        
                        <p class="text-center text-muted small mt-3">
                            <i class="fas fa-shield-alt me-1"></i>Your payment information is encrypted and processed securely by Razorpay
                        </p>
                    </div>
                    
                    <?php else: ?>
                    <!-- Demo / Fallback Payment Methods -->
                    <h4 class="fw-bold mb-3"><i class="fas fa-credit-card text-primary me-2"></i>Select Payment Method</h4>
                    <p class="text-muted small mb-3">
                        <i class="fas fa-flask me-1"></i>Demo mode: Razorpay API keys not configured. These are simulated payments.
                    </p>
                    
                    <form method="POST" action="" id="paymentForm">
                        <div class="payment-methods mb-4">
                            <!-- UPI Option -->
                            <div class="payment-option card mb-3">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" value="upi" id="methodUPI" onchange="togglePaymentForm()">
                                        <label class="form-check-label fw-bold" for="methodUPI">
                                            <i class="fas fa-mobile-alt text-success me-2"></i>UPI Payment
                                        </label>
                                    </div>
                                    <div id="upiForm" class="payment-detail-form mt-3" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">UPI ID</label>
                                                <input type="text" name="upi_id" class="form-control" placeholder="e.g. name@upi">
                                            </div>
                                            <div class="col-md-6 text-center">
                                                <div class="border p-3 rounded-3 bg-light">
                                                    <p class="small text-muted mb-2">Scan with any UPI app</p>
                                                    <div style="width: 150px; height: 150px; margin: 0 auto; background: #fff; display: flex; align-items: center; justify-content: center; border: 2px dashed #dee2e6;">
                                                        <i class="fas fa-qrcode fa-4x text-muted"></i>
                                                    </div>
                                                    <p class="small text-muted mt-2">UPI ID: khodiyar@upi</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Card Payment -->
                            <div class="payment-option card mb-3">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" value="card" id="methodCard" onchange="togglePaymentForm()">
                                        <label class="form-check-label fw-bold" for="methodCard">
                                            <i class="fas fa-credit-card text-primary me-2"></i>Card Payment
                                        </label>
                                    </div>
                                    <div id="cardForm" class="payment-detail-form mt-3" style="display: none;">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Card Number <span class="text-danger">*</span></label>
                                                <input type="text" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19" oninput="formatCardNumber(this)">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Card Type</label>
                                                <select name="card_type" class="form-select">
                                                    <option value="Visa">Visa</option>
                                                    <option value="Mastercard">Mastercard</option>
                                                    <option value="RuPay">RuPay</option>
                                                    <option value="Amex">American Express</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Expiry <span class="text-danger">*</span></label>
                                                <input type="text" name="card_expiry" class="form-control" placeholder="MM/YY" maxlength="5">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">CVV <span class="text-danger">*</span></label>
                                                <input type="password" name="card_cvv" class="form-control" placeholder="***" maxlength="4">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Net Banking -->
                            <div class="payment-option card mb-3">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" value="net_banking" id="methodNetBanking" onchange="togglePaymentForm()">
                                        <label class="form-check-label fw-bold" for="methodNetBanking">
                                            <i class="fas fa-university text-info me-2"></i>Net Banking
                                        </label>
                                    </div>
                                    <div id="netBankingForm" class="payment-detail-form mt-3" style="display: none;">
                                        <label class="form-label">Select Bank <span class="text-danger">*</span></label>
                                        <select name="bank_name" class="form-select">
                                            <option value="">-- Select Bank --</option>
                                            <option value="SBI">State Bank of India</option>
                                            <option value="HDFC">HDFC Bank</option>
                                            <option value="ICICI">ICICI Bank</option>
                                            <option value="Axis">Axis Bank</option>
                                            <option value="Kotak">Kotak Mahindra</option>
                                            <option value="Yes">Yes Bank</option>
                                            <option value="PNB">Punjab National Bank</option>
                                            <option value="BOB">Bank of Baroda</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Cash on Service -->
                            <div class="payment-option card mb-3">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" value="cash" id="methodCash" onchange="togglePaymentForm()">
                                        <label class="form-check-label fw-bold" for="methodCash">
                                            <i class="fas fa-money-bill-wave text-warning me-2"></i>Pay at Service
                                        </label>
                                    </div>
                                    <div id="cashForm" class="payment-detail-form mt-3" style="display: none;">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle me-2"></i>You can pay in cash when our team arrives for the service. No online payment required now.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary-gradient btn-lg py-3" id="payButton">
                                <i class="fas fa-lock me-2"></i>Pay ₹<?php echo number_format($amount); ?>
                            </button>
                            <p class="text-center text-muted small mt-2">
                                <i class="fas fa-shield-alt me-1"></i>Your payment is secure and encrypted
                            </p>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($useRazorpay && $razorpayOrder): ?>
<!-- Razorpay Checkout Integration -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var rzpOrderId = <?php echo json_encode($razorpayOrder['id']); ?>;
var rzpKeyId = <?php echo json_encode($razorpaySettings['key_id']); ?>;
var rzpAmount = <?php echo json_encode($amountInPaise); ?>;
var rzpBookingId = <?php echo json_encode($bookingId); ?>;
var rzpCustomerName = <?php echo json_encode($booking['customer_name']); ?>;
var rzpCustomerEmail = <?php echo json_encode($booking['customer_email']); ?>;
var rzpCustomerPhone = <?php echo json_encode($booking['customer_phone']); ?>;

function openRazorpayCheckout() {
    var options = {
        key: rzpKeyId,
        amount: rzpAmount,
        currency: "INR",
        name: "Khodiyar Computer",
        description: "Booking Payment - " + rzpBookingId,
        order_id: rzpOrderId,
        handler: function (response) {
            // Redirect to server-side verification
            var url = 'payment-verify.php?booking=' + encodeURIComponent(rzpBookingId) +
                '&payment_id=' + encodeURIComponent(response.razorpay_payment_id) +
                '&order_id=' + encodeURIComponent(response.razorpay_order_id) +
                '&signature=' + encodeURIComponent(response.razorpay_signature);
            window.location.href = url;
        },
        prefill: {
            name: rzpCustomerName,
            email: rzpCustomerEmail,
            contact: rzpCustomerPhone
        },
        theme: {
            color: "#6C3CE1"
        },
        modal: {
            ondismiss: function() {
                // User closed the payment modal
                alert('Payment cancelled. You can retry or pay later at the service.');
            }
        }
    };
    
    var rzp = new Razorpay(options);
    rzp.on('payment.failed', function (response) {
        var errUrl = 'payment-failed.php?booking=' + encodeURIComponent(rzpBookingId) +
            '&amount=' + encodeURIComponent(rzpAmount / 100) +
            '&error=' + encodeURIComponent(response.error.description || 'Payment failed');
        window.location.href = errUrl;
    });
    rzp.open();
    
    // Disable button to prevent double clicks
    document.getElementById('rzpPayButton').disabled = true;
    document.getElementById('rzpPayButton').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
}
</script>
<?php endif; ?>

<script>
function togglePaymentForm() {
    // Hide all payment detail forms
    document.querySelectorAll('.payment-detail-form').forEach(function(form) {
        form.style.display = 'none';
    });
    
    // Show selected payment form
    var selectedMethod = document.querySelector('input[name="payment_method"]:checked');
    if (selectedMethod) {
        var targetForm = document.getElementById(selectedMethod.value + 'Form');
        if (targetForm) {
            targetForm.style.display = 'block';
        }
    }
}

function formatCardNumber(input) {
    var value = input.value.replace(/\D/g, '');
    var formatted = '';
    for (var i = 0; i < value.length && i < 16; i++) {
        if (i > 0 && i % 4 === 0) {
            formatted += ' ';
        }
        formatted += value[i];
    }
    input.value = formatted;
}

// Initialize if a method is pre-selected
togglePaymentForm();
</script>

<style>
.payment-option {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.payment-option:hover {
    border-color: var(--primary);
    box-shadow: 0 2px 10px rgba(108, 60, 225, 0.1);
}

.payment-option .form-check-input:checked ~ .form-check-label {
    color: var(--primary);
}

.payment-option:has(.form-check-input:checked) {
    border-color: var(--primary);
    background: rgba(108, 60, 225, 0.02);
}

.payment-badge {
    background: #f8f9fa;
    font-size: 0.9rem;
    font-weight: 500;
}
</style>

<?php include 'includes/footer.php'; ?>

