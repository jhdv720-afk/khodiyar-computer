<?php
/**
 * Session Configuration & Management
 * Khodiyar Computer - IT Services
 */

class DatabaseSessionHandler implements SessionHandlerInterface {
    public function open(string $path, string $name): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read(string $id): string|false {
        $session = dbFetchOne(
            'SELECT session_data FROM php_sessions WHERE session_id = ? AND expires_at > ?',
            [$id, time()]
        );
        return $session['session_data'] ?? '';
    }

    public function write(string $id, string $data): bool {
        $expiresAt = time() + (int)ini_get('session.gc_maxlifetime');
        dbQuery(
            'INSERT INTO php_sessions (session_id, session_data, expires_at) VALUES (?, ?, ?) '
            . 'ON DUPLICATE KEY UPDATE session_data = VALUES(session_data), expires_at = VALUES(expires_at)',
            [$id, $data, $expiresAt]
        );
        return true;
    }

    public function destroy(string $id): bool {
        dbQuery('DELETE FROM php_sessions WHERE session_id = ?', [$id]);
        return true;
    }

    public function gc(int $maxLifetime): int|false {
        return dbQuery('DELETE FROM php_sessions WHERE expires_at < ?', [time()])->rowCount();
    }
}

// Vercel instances are ephemeral, so keep session state in the shared database.
if (getenv('VERCEL') || getenv('SESSION_STORAGE') === 'mysql') {
    session_set_save_handler(new DatabaseSessionHandler(), true);
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Set a flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // 'success', 'error', 'warning', 'info'
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $msg;
    }
    return null;
}

/**
 * Display flash message as HTML
 */
function displayFlashMessage() {
    $msg = getFlashMessage();
    if ($msg) {
        $icons = [
            'success' => 'fa-check-circle',
            'error' => 'fa-times-circle',
            'warning' => 'fa-exclamation-triangle',
            'info' => 'fa-info-circle'
        ];
        $icon = $icons[$msg['type']] ?? 'fa-info-circle';
        echo '<div class="alert alert-' . $msg['type'] . ' alert-dismissible fade show" role="alert">
                <i class="fas ' . $icon . ' me-2"></i>' . htmlspecialchars($msg['message']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
}

/**
 * Check if user is logged in
 */
function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require user authentication - redirect if not logged in
 */
function requireUserLogin() {
    if (!isUserLoggedIn()) {
        setFlashMessage('warning', 'Please login to access this page.');
        header('Location: login.php');
        exit;
    }
}

/**
 * Require admin authentication - redirect if not logged in
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        setFlashMessage('warning', 'Please login to access admin panel.');
        header('Location: login.php');
        exit;
    }
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (isUserLoggedIn()) {
        return dbFetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    }
    return null;
}

/**
 * Get current admin data
 */
function getCurrentAdmin() {
    if (isAdminLoggedIn()) {
        return dbFetchOne("SELECT * FROM admins WHERE id = ?", [$_SESSION['admin_id']]);
    }
    return null;
}

/**
 * Logout user
 */
function logoutUser() {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_logged_in']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_email']);
    session_destroy();
}

/**
 * Logout admin
 */
function logoutAdmin() {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_name']);
    unset($_SESSION['admin_role']);
    session_destroy();
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate a unique booking ID
 */
function generateBookingId() {
    $prefix = 'KHB';
    $timestamp = time();
    $random = strtoupper(substr(uniqid(), -4));
    return $prefix . date('ymd', $timestamp) . $random;
}

