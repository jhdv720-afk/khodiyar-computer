<?php
/**
 * Helper Functions
 * Khodiyar Computer - IT Services
 */

/**
 * Sanitize input data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number (Indian format)
 */
function validatePhone($phone) {
    return preg_match('/^[0-9]{10}$/', $phone);
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Get all active services
 */
function getActiveServices() {
    return dbFetchAll("SELECT * FROM services WHERE status = 'active' ORDER BY display_order ASC, id ASC");
}

/**
 * Get service by slug
 */
function getServiceBySlug($slug) {
    return dbFetchOne("SELECT * FROM services WHERE slug = ? AND status = 'active'", [$slug]);
}

/**
 * Get service by ID
 */
function getServiceById($id) {
    return dbFetchOne("SELECT * FROM services WHERE id = ?", [$id]);
}

/**
 * Get total count of bookings
 */
function getBookingCount($status = null) {
    $sql = "SELECT COUNT(*) as count FROM bookings";
    $params = [];
    if ($status) {
        $sql .= " WHERE status = ?";
        $params[] = $status;
    }
    return dbFetchOne($sql, $params)['count'];
}

/**
 * Get total count of users
 */
function getUserCount() {
    return dbFetchOne("SELECT COUNT(*) as count FROM users")['count'];
}

/**
 * Get total count of messages
 */
function getMessageCount($unreadOnly = false) {
    $sql = "SELECT COUNT(*) as count FROM contacts";
    if ($unreadOnly) {
        $sql .= " WHERE is_read = 0";
    }
    return dbFetchOne($sql)['count'];
}

/**
 * Get recent bookings
 */
function getRecentBookings($limit = 5) {
    return dbFetchAll(
        "SELECT b.*, s.title as service_name 
         FROM bookings b 
         LEFT JOIN services s ON b.service_id = s.id 
         ORDER BY b.created_at DESC 
         LIMIT ?",
        [$limit]
    );
}

/**
 * Time elapsed function
 */
function timeElapsed($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' mins ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('d M Y', $timestamp);
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '₹' . number_format($amount);
}

/**
 * Get status badge class
 */
function getStatusBadge($status) {
    $badges = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'in_progress' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger',
        'rejected' => 'danger',
        'active' => 'success',
        'inactive' => 'secondary'
    ];
    return $badges[$status] ?? 'secondary';
}

/**
 * Truncate text
 */
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * Get setting value
 */
function getSetting($key) {
    $result = dbFetchOne("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
    return $result ? $result['setting_value'] : null;
}

/**
 * Generate slug from string
 */
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Generate a unique payment ID
 */
function generatePaymentId() {
    $prefix = 'PAY';
    $timestamp = time();
    $random = strtoupper(substr(uniqid(), -6));
    return $prefix . date('ymd', $timestamp) . $random;
}

/**
 * Get all active testimonials
 */
function getActiveTestimonials($limit = null) {
    $sql = "SELECT t.*, s.title as service_name FROM testimonials t 
            LEFT JOIN services s ON t.service_id = s.id 
            WHERE t.status = 'active' 
            ORDER BY t.display_order ASC, t.created_at DESC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    return dbFetchAll($sql);
}

/**
 * Get published blog posts
 */
function getPublishedPosts($limit = null) {
    $sql = "SELECT * FROM blog_posts WHERE status = 'published' 
            ORDER BY published_at DESC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    return dbFetchAll($sql);
}

/**
 * Get blog post by slug
 */
function getPostBySlug($slug) {
    return dbFetchOne("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published'", [$slug]);
}

/**
 * Get recent blog posts (excluding current)
 */
function getRecentPosts($excludeId = null, $limit = 3) {
    $sql = "SELECT * FROM blog_posts WHERE status = 'published'";
    $params = [];
    if ($excludeId) {
        $sql .= " AND id != ?";
        $params[] = $excludeId;
    }
    $sql .= " ORDER BY published_at DESC LIMIT " . (int)$limit;
    return dbFetchAll($sql, $params);
}

/**
 * Increment blog post view count
 */
function incrementPostViews($id) {
    dbQuery("UPDATE blog_posts SET views = views + 1 WHERE id = ?", [$id]);
}

/**
 * Subscribe to newsletter
 */
function subscribeNewsletter($email, $name = null) {
    $existing = dbFetchOne("SELECT id, status FROM newsletter_subscribers WHERE email = ?", [$email]);
    if ($existing) {
        if ($existing['status'] === 'unsubscribed') {
            dbQuery("UPDATE newsletter_subscribers SET status = 'active', name = ?, unsubscribed_at = NULL WHERE email = ?", [$name, $email]);
        }
        return ['success' => false, 'message' => 'Email is already subscribed.'];
    }
    dbInsert("INSERT INTO newsletter_subscribers (email, name) VALUES (?, ?)", [$email, $name]);
    return ['success' => true, 'message' => 'Subscribed successfully!'];
}

/**
 * Send email using PHPMailer (with graceful fallback if PHPMailer files are missing)
 */
function sendEmail($to, $subject, $body, $altBody = '') {
    $mailerDir = __DIR__ . '/../includes/phpmailer/';
    
    // If PHPMailer files don't exist, use native mail() as fallback
    if (!file_exists($mailerDir . 'PHPMailer.php')) {
        try {
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . (getSetting('company_name') ?: 'Khodiyar Computer') . " <" . (getSetting('company_email') ?: 'noreply@khodiyarcomputer.com') . ">\r\n";
            if (@mail($to, $subject, $body, $headers)) {
                return ['success' => true, 'message' => 'Email sent successfully!'];
            }
            return ['success' => false, 'message' => 'Email could not be sent via mail().'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Email could not be sent. Error: ' . $e->getMessage()];
        }
    }
    
    require_once $mailerDir . 'PHPMailer.php';
    require_once $mailerDir . 'SMTP.php';
    require_once $mailerDir . 'Exception.php';
    
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $smtpHost = getSetting('smtp_host') ?: '';
        $smtpPort = getSetting('smtp_port') ?: '587';
        $smtpUser = getSetting('smtp_user') ?: '';
        $smtpPass = getSetting('smtp_pass') ?: '';
        $smtpEncryption = getSetting('smtp_encryption') ?: 'tls';
        $fromEmail = getSetting('company_email') ?: 'noreply@khodiyarcomputer.com';
        $fromName = getSetting('company_name') ?: 'Khodiyar Computer';
        
        if ($smtpHost) {
            // SMTP mode
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
            $mail->SMTPSecure = $smtpEncryption;
            $mail->Port = $smtpPort;
        } else {
            // PHP mail() fallback
            $mail->isMail();
        }
        
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);
        
        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully!'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Email could not be sent. Error: ' . $mail->ErrorInfo];
    }
}

/**
 * Send booking confirmation email
 */
function sendBookingConfirmation($booking) {
    $subject = 'Booking Confirmed - ' . $booking['booking_id'];
    $body = "
    <html>
    <body style='font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px;'>
        <div style='max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
            <div style='background: linear-gradient(135deg, #6C3CE1, #00D2FF); padding: 30px; text-align: center;'>
                <h2 style='color: #fff; margin: 0;'>Booking Confirmed!</h2>
            </div>
            <div style='padding: 30px;'>
                <p>Dear <strong>" . htmlspecialchars($booking['customer_name']) . "</strong>,</p>
                <p>Your service booking has been confirmed. Here are the details:</p>
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr><td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Booking ID:</strong></td><td style='padding: 10px; border-bottom: 1px solid #eee;'>" . $booking['booking_id'] . "</td></tr>
                    <tr><td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Service:</strong></td><td style='padding: 10px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($booking['service_name'] ?? 'N/A') . "</td></tr>
                    <tr><td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Preferred Date:</strong></td><td style='padding: 10px; border-bottom: 1px solid #eee;'>" . date('d M Y', strtotime($booking['preferred_date'])) . "</td></tr>
                    <tr><td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Preferred Time:</strong></td><td style='padding: 10px; border-bottom: 1px solid #eee;'>" . ($booking['preferred_time'] ? date('h:i A', strtotime($booking['preferred_time'])) : 'Flexible') . "</td></tr>
                </table>
                <p>We will contact you shortly to confirm the appointment.</p>
                <p>Thank you for choosing Khodiyar Computer!</p>
                <p style='color: #6c757d; font-size: 12px; margin-top: 20px;'>This is an automated message. Please do not reply directly.</p>
            </div>
        </div>
    </body>
    </html>";
    
    return sendEmail($booking['customer_email'], $subject, $body);
}

/**
 * Send booking status update email
 */
function sendBookingStatusUpdate($booking, $oldStatus = '') {
    $statusLabels = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'rejected' => 'Rejected'
    ];
    
    $currentStatus = $statusLabels[$booking['status']] ?? ucfirst($booking['status']);
    
    $subject = 'Booking Status Update - ' . $booking['booking_id'];
    $body = "
    <html>
    <body style='font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px;'>
        <div style='max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
            <div style='background: linear-gradient(135deg, #6C3CE1, #00D2FF); padding: 30px; text-align: center;'>
                <h2 style='color: #fff; margin: 0;'>Booking Status Updated</h2>
            </div>
            <div style='padding: 30px;'>
                <p>Dear <strong>" . htmlspecialchars($booking['customer_name']) . "</strong>,</p>
                <p>Your booking status has been updated.</p>
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr><td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Booking ID:</strong></td><td style='padding: 10px; border-bottom: 1px solid #eee;'>" . $booking['booking_id'] . "</td></tr>
                    <tr><td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>New Status:</strong></td><td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>" . $currentStatus . "</strong></td></tr>
                    " . (!empty($booking['admin_notes']) ? "<tr><td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Admin Notes:</strong></td><td style='padding: 10px; border-bottom: 1px solid #eee;'>" . nl2br(htmlspecialchars($booking['admin_notes'])) . "</td></tr>" : "") . "
                </table>
                <p>Thank you for choosing Khodiyar Computer!</p>
                <p style='color: #6c757d; font-size: 12px; margin-top: 20px;'>This is an automated message. Please do not reply directly.</p>
            </div>
        </div>
    </body>
    </html>";
    
    return sendEmail($booking['customer_email'], $subject, $body);
}

/**
 * Get payments for a user
 */
function getUserPayments($userId) {
    return dbFetchAll(
        "SELECT p.*, b.service_id, s.title as service_name 
         FROM payments p 
         LEFT JOIN bookings b ON p.booking_id = b.booking_id 
         LEFT JOIN services s ON b.service_id = s.id 
         WHERE p.user_id = ? 
         ORDER BY p.created_at DESC",
        [$userId]
    );
}

/**
 * Get payment method badge
 */
function getPaymentMethodBadge($method) {
    $badges = [
        'card' => 'primary',
        'net_banking' => 'info',
        'upi' => 'success',
        'cash' => 'warning',
        'razorpay' => 'dark'
    ];
    return $badges[$method] ?? 'secondary';
}

/**
 * Get payment status badge
 */
function getPaymentStatusBadge($status) {
    $badges = [
        'pending' => 'warning',
        'paid' => 'success',
        'failed' => 'danger',
        'refunded' => 'info',
        'cancelled' => 'secondary'
    ];
    return $badges[$status] ?? 'secondary';
}

/**
 * Upload file
 */
function uploadFile($file, $targetDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp']) {
    if (!isset($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload failed.'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type.'];
    }
    
    $filename = uniqid() . '.' . $ext;

    if (getenv('BLOB_READ_WRITE_TOKEN')) {
        if (!function_exists('curl_init')) {
            return ['success' => false, 'message' => 'Vercel Blob uploads require the PHP cURL extension.'];
        }

        $pathname = 'uploads/' . $filename;
        $access = getenv('BLOB_ACCESS') === 'private' ? 'private' : 'public';
        $contentType = function_exists('mime_content_type')
            ? mime_content_type($file['tmp_name'])
            : 'application/octet-stream';
        $fileHandle = fopen($file['tmp_name'], 'rb');
        if ($fileHandle === false) {
            return ['success' => false, 'message' => 'Failed to read the uploaded file.'];
        }

        $request = curl_init('https://vercel.com/api/blob/?pathname=' . rawurlencode($pathname));
        if ($request === false) {
            fclose($fileHandle);
            return ['success' => false, 'message' => 'Failed to initialize the Vercel Blob request.'];
        }

        curl_setopt_array($request, [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_UPLOAD => true,
            CURLOPT_INFILE => $fileHandle,
            CURLOPT_INFILESIZE => filesize($file['tmp_name']),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . getenv('BLOB_READ_WRITE_TOKEN'),
                'Content-Type: ' . $contentType,
                'x-content-type: ' . $contentType,
                'x-vercel-blob-access: ' . $access,
                'x-add-random-suffix: 1',
                'x-api-version: 12',
                'x-api-blob-request-attempt: 0',
                'x-api-blob-request-id: ' . bin2hex(random_bytes(16)),
                'x-content-length: ' . filesize($file['tmp_name']),
            ],
        ]);
        $response = curl_exec($request);
        $statusCode = curl_getinfo($request, CURLINFO_RESPONSE_CODE);
        $requestError = curl_error($request);
        curl_close($request);
        fclose($fileHandle);

        $blob = is_string($response) ? json_decode($response, true) : null;
        if ($statusCode < 200 || $statusCode >= 300 || !is_array($blob) || empty($blob['url'])) {
            return [
                'success' => false,
                'message' => $requestError ?: (is_array($blob)
                    ? ($blob['error'] ?? $blob['message'] ?? 'Failed to save file to Vercel Blob.')
                    : 'Failed to save file to Vercel Blob.'),
            ];
        }

        return ['success' => true, 'filename' => $filename, 'url' => $blob['url']];
    }

    if (getenv('VERCEL')) {
        return ['success' => false, 'message' => 'Set BLOB_READ_WRITE_TOKEN to enable persistent uploads.'];
    }

    $targetPath = $targetDir . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename, 'url' => '/assets/uploads/' . $filename];
    }
    
    return ['success' => false, 'message' => 'Failed to save file.'];
}

function uploadImageField($fieldName, $currentUrl = '') {
    if (!isset($_FILES[$fieldName]) || ($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $currentUrl;
    }

    $upload = uploadFile($_FILES[$fieldName], BASE_PATH . '/assets/uploads');
    if (!$upload['success']) {
        throw new RuntimeException($upload['message']);
    }

    return $upload['url'];
}

/**
 * Check if request is AJAX
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Send JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Redirect with message
 */
function redirectWith($url, $type, $message) {
    setFlashMessage($type, $message);
    header("Location: $url");
    exit;
}

/* ============================================================
 * RAZORPAY PAYMENT GATEWAY HELPERS
 * ============================================================ */

/**
 * Get Razorpay settings from the database
 */
function getRazorpaySettings() {
    $keyId = getSetting('razorpay_key_id') ?: '';
    $keySecret = getSetting('razorpay_key_secret') ?: '';
    $testMode = getSetting('razorpay_test_mode');
    $enabled = getSetting('razorpay_enabled');
    
    return [
        'key_id' => $keyId,
        'key_secret' => $keySecret,
        'test_mode' => ($testMode === null) ? true : (bool)$testMode,
        'enabled' => ($enabled === null) ? true : (bool)$enabled
    ];
}

/**
 * Check if Razorpay is configured with API keys
 */
function isRazorpayConfigured() {
    $settings = getRazorpaySettings();
    return !empty($settings['key_id']) && !empty($settings['key_secret']);
}

/**
 * Check if Razorpay is enabled in admin settings
 */
function isRazorpayEnabled() {
    $settings = getRazorpaySettings();
    return $settings['enabled'];
}

/**
 * Get the base URL for the current site (used for callbacks)
 */
function getSiteBaseUrl() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    
    // If in a subdirectory (like /khodiyar-computer), get the root
    $base = preg_replace('#/[^/]*$#', '', $scriptDir);
    
    return $scheme . '://' . $host . $base;
}

/**
 * Create a Razorpay Order via the Orders API
 * Returns array with success flag and order data
 */
function createRazorpayOrder($amountInPaise, $receipt, $notes = []) {
    $settings = getRazorpaySettings();
    
    if (!isRazorpayConfigured()) {
        return ['success' => false, 'message' => 'Razorpay is not configured. Please set API keys in admin settings.'];
    }
    
    $url = 'https://api.razorpay.com/v1/orders';
    
    $postData = [
        'amount' => (int)$amountInPaise,
        'currency' => 'INR',
        'receipt' => $receipt,
        'notes' => $notes
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_USERPWD, $settings['key_id'] . ':' . $settings['key_secret']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For localhost testing
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        return ['success' => false, 'message' => 'cURL error: ' . $curlError];
    }
    
    $data = json_decode($response, true);
    
    if ($httpCode >= 200 && $httpCode < 300 && isset($data['id'])) {
        return [
            'success' => true,
            'order' => $data
        ];
    }
    
    $errorMsg = $data['error']['description'] ?? ('Razorpay API error (HTTP ' . $httpCode . ')');
    return ['success' => false, 'message' => $errorMsg];
}

/**
 * Verify Razorpay payment signature (server-side)
 */
function verifyRazorpaySignature($orderId, $paymentId, $signature) {
    $settings = getRazorpaySettings();
    
    if (empty($settings['key_secret'])) {
        return false;
    }
    
    $generatedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $settings['key_secret']);
    
    return hash_equals($generatedSignature, $signature);
}

/**
 * Fetch payment details from Razorpay API
 */
function getRazorpayPayment($paymentId) {
    $settings = getRazorpaySettings();
    
    if (!isRazorpayConfigured()) {
        return null;
    }
    
    $url = 'https://api.razorpay.com/v1/payments/' . urlencode($paymentId);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $settings['key_id'] . ':' . $settings['key_secret']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    }
    
    return null;
}

/**
 * Capture/confirm a Razorpay payment (authorized → captured)
 */
function captureRazorpayPayment($paymentId, $amountInPaise) {
    $settings = getRazorpaySettings();
    
    if (!isRazorpayConfigured()) {
        return ['success' => false, 'message' => 'Razorpay is not configured.'];
    }
    
    $url = 'https://api.razorpay.com/v1/payments/' . urlencode($paymentId) . '/capture';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['amount' => (int)$amountInPaise, 'currency' => 'INR']));
    curl_setopt($ch, CURLOPT_USERPWD, $settings['key_id'] . ':' . $settings['key_secret']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return ['success' => true, 'data' => json_decode($response, true)];
    }
    
    return ['success' => false, 'message' => 'Payment capture failed (HTTP ' . $httpCode . ')'];
}

