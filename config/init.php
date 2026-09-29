<?php
/**
 * Initialization file - Include this at the top of every page
 * Khodiyar Computer - IT Services
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', getenv('VERCEL') ? '0' : '1');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Base path
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', '/khodiyar-computer');

// Load config files
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/helpers.php';

// Auto-load database and start session
$db = getConnection();

