<?php
/**
 * Application Configuration
 * 
 * Core configuration for the Hotel Master Lite system.
 * This file should be required at the start of every request.
 */

// Load environment variables if .env.php exists
$envPath = dirname(__DIR__) . '/.env.php';
if (file_exists($envPath)) {
    $config = require $envPath;
} else {
    $config = [];
}

// Load constants
require_once dirname(__DIR__) . '/config/constants.php';

// Error Reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_PATH . '/error.log');
}

// Set secure headers
function setSafeHeaders() {
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Enable XSS protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // Content Security Policy
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:;");
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Permissions Policy
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// Session Configuration
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', SESSION_COOKIE_HTTPONLY ? 1 : 0);
ini_set('session.cookie_secure', SESSION_COOKIE_SECURE ? 1 : 0);
ini_set('session.cookie_samesite', SESSION_COOKIE_SAMESITE);
ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create storage directories if they don't exist
$storageDirs = [LOGS_PATH, EXPORTS_PATH, BACKUPS_PATH, UPLOADS_PATH];
foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Set safe headers
setSafeHeaders();

// Determine if this is an API request
define('IS_API_REQUEST', strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0);

// Return configuration for use
return $config;
