<?php
/**
 * Payment Verification Callback
 * Securely verifies Razorpay payment signature before marking as paid.
 * Redirects to payment-success.php on success or payment-failed.php on failure.
 */
require_once 'config/init.php';

$bookingId = $_GET['booking'] ?? '';
$razorpayPaymentId = $_GET['payment_id'] ?? '';
$razorpayOrderId = $_GET['order_id'] ?? '';
$razorpaySignature = $_GET['signature'] ?? '';

if (empty($bookingId) || empty($razorpayPaymentId) || empty($razorpayOrderId) || empty($razorpaySignature)) {
    header('Location: payment-failed.php?booking=' . urlencode($bookingId) . '&error=' . urlencode('Invalid payment response. Missing required parameters.'));
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

// Verify the signature server-side
$signatureValid = verifyRazorpaySignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature);

if (!$signatureValid) {
    // Signature verification failed - do NOT mark as paid
    dbInsert(
        "INSERT INTO payments (payment_id, booking_id, customer_name, customer_email, customer_phone, 
         amount, payment_method, payment_status, payment_response, transaction_ref) 
         VALUES (?, ?, ?, ?, ?, ?, 'razorpay', 'failed', ?, ?)",
        [
            generatePaymentId(), $bookingId, $booking['customer_name'], $booking['customer_email'],
            $booking['customer_phone'], $booking['estimated_budget'] ?? 0, 
            'Signature verification failed. Order: ' . $razorpayOrderId,
            $razorpayPaymentId
        ]
    );
    header('Location: payment-failed.php?booking=' . urlencode($bookingId) . '&error=' . urlencode('Payment verification failed. Please try again.'));
    exit;
}

// Fetch payment details from Razorpay to confirm amount and status
$rzpPayment = getRazorpayPayment($razorpayPaymentId);

$amount = $booking['estimated_budget'] ?? 0;
$amountInPaise = (int)round($amount * 100);

if ($rzpPayment) {
    // Verify the payment amount matches
    if ((int)$rzpPayment['amount'] !== $amountInPaise) {
        dbInsert(
            "INSERT INTO payments (payment_id, booking_id, customer_name, customer_email, customer_phone, 
             amount, payment_method, payment_status, payment_response, transaction_ref) 
             VALUES (?, ?, ?, ?, ?, ?, 'razorpay', 'failed', ?, ?)",
            [
                generatePaymentId(), $bookingId, $booking['customer_name'], $booking['customer_email'],
                $booking['customer_phone'], $amount,
                'Amount mismatch. Expected: ' . $amountInPaise . ', Got: ' . $rzpPayment['amount'],
                $razorpayPaymentId
            ]
        );
        header('Location: payment-failed.php?booking=' . urlencode($bookingId) . '&error=' . urlencode('Payment amount verification failed.'));
        exit;
    }
    
    // Check payment status
    $rzpStatus = $rzpPayment['status'] ?? '';
    
    if ($rzpStatus === 'captured' || $rzpStatus === 'authorized') {
        // If authorized, capture the payment
        if ($rzpStatus === 'authorized') {
            $captureResult = captureRazorpayPayment($razorpayPaymentId, $amountInPaise);
            if (!$captureResult['success']) {
                header('Location: payment-failed.php?booking=' . urlencode($bookingId) . '&error=' . urlencode('Payment capture failed.'));
                exit;
            }
            $rzpStatus = 'captured';
        }
        
        // Payment successful - insert payment record
        $paymentId = generatePaymentId();
        $userId = isUserLoggedIn() ? $_SESSION['user_id'] : null;
        
        // Get payment method info from Razorpay response
        $paymentMethod = 'razorpay';
        $methodInfo = $rzpPayment['method'] ?? '';
        $cardLastFour = null;
        $cardType = null;
        $bankName = null;
        $upiId = null;
        
        if ($methodInfo === 'card' && isset($rzpPayment['card'])) {
            $cardLastFour = $rzpPayment['card']['last4'] ?? null;
            $cardType = $rzpPayment['card']['network'] ?? null;
        } elseif ($methodInfo === 'netbanking') {
            $bankName = $rzpPayment['bank'] ?? null;
        } elseif ($methodInfo === 'upi') {
            $upiId = $rzpPayment['vpa'] ?? $rzpPayment['upi']['vpa'] ?? null;
        }
        
        dbInsert(
            "INSERT INTO payments (payment_id, booking_id, user_id, customer_name, customer_email, customer_phone, 
             amount, payment_method, card_last_four, card_type, bank_name, upi_id, transaction_ref, payment_status, payment_date, payment_response) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'paid', NOW(), ?)",
            [
                $paymentId, $bookingId, $userId, $booking['customer_name'], $booking['customer_email'],
                $booking['customer_phone'], $amount, $paymentMethod, $cardLastFour, $cardType,
                $bankName, $upiId, $razorpayPaymentId, json_encode([
                    'razorpay_order_id' => $razorpayOrderId,
                    'razorpay_payment_id' => $razorpayPaymentId,
                    'razorpay_signature' => $razorpaySignature,
                    'payment_status' => $rzpStatus,
                    'method' => $methodInfo
                ])
            ]
        );
        
        // Update booking payment status
        dbQuery(
            "UPDATE bookings SET payment_status = 'paid', payment_required = 0 WHERE booking_id = ?",
            [$bookingId]
        );
        
        // Clear session order data
        unset($_SESSION['razorpay_order_id']);
        unset($_SESSION['razorpay_amount']);
        unset($_SESSION['razorpay_booking_id']);
        
        // Send confirmation email
        sendBookingConfirmation($booking);
        
        // Redirect to success page
        header('Location: payment-success.php?payment=' . $paymentId . '&booking=' . $bookingId);
        exit;
        
    } else {
        // Payment not successful
        dbInsert(
            "INSERT INTO payments (payment_id, booking_id, customer_name, customer_email, customer_phone, 
             amount, payment_method, payment_status, payment_response, transaction_ref) 
             VALUES (?, ?, ?, ?, ?, ?, 'razorpay', 'failed', ?, ?)",
            [
                generatePaymentId(), $bookingId, $booking['customer_name'], $booking['customer_email'],
                $booking['customer_phone'], $amount,
                'Razorpay payment status: ' . $rzpStatus,
                $razorpayPaymentId
            ]
        );
        header('Location: payment-failed.php?booking=' . urlencode($bookingId) . '&amount=' . urlencode($amount) . '&error=' . urlencode('Payment was not completed. Status: ' . $rzpStatus));
        exit;
    }
    
} else {
    // Could not fetch payment from Razorpay - but signature was valid, so mark as paid
    $paymentId = generatePaymentId();
    $userId = isUserLoggedIn() ? $_SESSION['user_id'] : null;
    
    dbInsert(
        "INSERT INTO payments (payment_id, booking_id, user_id, customer_name, customer_email, customer_phone, 
         amount, payment_method, transaction_ref, payment_status, payment_date, payment_response) 
         VALUES (?, ?, ?, ?, ?, ?, ?, 'razorpay', ?, 'paid', NOW(), ?)",
        [
            $paymentId, $bookingId, $userId, $booking['customer_name'], $booking['customer_email'],
            $booking['customer_phone'], $amount, $razorpayPaymentId, json_encode([
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature
            ])
        ]
    );
    
    dbQuery(
        "UPDATE bookings SET payment_status = 'paid', payment_required = 0 WHERE booking_id = ?",
        [$bookingId]
    );
    
    unset($_SESSION['razorpay_order_id']);
    unset($_SESSION['razorpay_amount']);
    unset($_SESSION['razorpay_booking_id']);
    
    sendBookingConfirmation($booking);
    
    header('Location: payment-success.php?payment=' . $paymentId . '&booking=' . $bookingId);
    exit;
}

