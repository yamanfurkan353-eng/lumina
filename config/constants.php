<?php
/**
 * Application Constants
 * 
 * Core application-wide constants and settings.
 */

// Application Info
define('APP_NAME', 'Hotel Master Lite');
define('APP_VERSION', '1.0.0');
define('APP_AUTHOR', 'Hotel Master Team');

// Paths
define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', APP_ROOT . '/public');
define('DATABASE_PATH', APP_ROOT . '/database/hotel.db');
define('STORAGE_PATH', APP_ROOT . '/storage');
define('LOGS_PATH', STORAGE_PATH . '/logs');
define('EXPORTS_PATH', STORAGE_PATH . '/exports');
define('BACKUPS_PATH', STORAGE_PATH . '/backups');
define('UPLOADS_PATH', STORAGE_PATH . '/uploads');

// URLs
define('BASE_URL', (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']);
define('API_BASE_URL', BASE_URL . '/api');

// Environment
define('USE_PRODUCTION', getenv('APP_ENV') === 'production');
define('DEBUG_MODE', !USE_PRODUCTION);

// Session
define('SESSION_TIMEOUT', 3600); // 1 hour
define('SESSION_COOKIE_SECURE', USE_PRODUCTION);
define('SESSION_COOKIE_HTTPONLY', true);
define('SESSION_COOKIE_SAMESITE', 'Strict');

// Database
define('DB_TYPE', 'sqlite');
define('DB_PATH', DATABASE_PATH);

// DateTime
define('DATE_FORMAT', 'd.m.Y');
define('TIME_FORMAT', 'H:i');
define('DATETIME_FORMAT', 'd.m.Y H:i:s');
date_default_timezone_set('Europe/Istanbul');

// Currency
define('CURRENCY', 'TRY');
define('CURRENCY_SYMBOL', '₺');
define('DECIMAL_SEPARATOR', ',');
define('THOUSANDS_SEPARATOR', '.');

// Room Status
define('ROOM_STATUS_AVAILABLE', 'available');
define('ROOM_STATUS_OCCUPIED', 'occupied');
define('ROOM_STATUS_DIRTY', 'dirty');
define('ROOM_STATUS_MAINTENANCE', 'maintenance');

define('ROOM_STATUSES', [
    'available' => 'Müsait',
    'occupied' => 'Dolu',
    'dirty' => 'Kirli',
    'maintenance' => 'Bakımda'
]);

// Reservation Status
define('RESERVATION_STATUS_CONFIRMED', 'confirmed');
define('RESERVATION_STATUS_CHECKED_IN', 'checked_in');
define('RESERVATION_STATUS_CHECKED_OUT', 'checked_out');
define('RESERVATION_STATUS_CANCELLED', 'cancelled');

define('RESERVATION_STATUSES', [
    'confirmed' => 'Onaylanmış',
    'checked_in' => 'Misafir Konaklamada',
    'checked_out' => 'Ayrılmış',
    'cancelled' => 'İptal Edilmiş'
]);

// Payment Status
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_PARTIAL', 'partial');
define('PAYMENT_PAID', 'paid');

define('PAYMENT_STATUSES', [
    'pending' => 'Beklemede',
    'partial' => 'Kısmi Ödeme',
    'paid' => 'Ödendi'
]);

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_RECEPTIONIST', 'receptionist');
define('ROLE_HOUSEKEEPING', 'housekeeping');

define('USER_ROLES', [
    'admin' => 'Yönetici',
    'receptionist' => 'Resepsiyon',
    'housekeeping' => 'Oda Temizliği'
]);

// Room Types
define('ROOM_TYPE_SINGLE', 'single');
define('ROOM_TYPE_DOUBLE', 'double');
define('ROOM_TYPE_SUITE', 'suite');
define('ROOM_TYPE_DELUXE', 'deluxe');

define('ROOM_TYPES', [
    'single' => 'Tek Kişi',
    'double' => 'Çift Kişi',
    'suite' => 'Suit',
    'deluxe' => 'Deluxe'
]);

// Pagination
define('ITEMS_PER_PAGE', 20);

// File Upload
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_UPLOAD_TYPES', ['jpg', 'jpeg', 'png', 'pdf']);

// Rate Limiting
define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_REQUESTS', 100);
define('RATE_LIMIT_WINDOW', 60); // seconds

// Check-in/Check-out Times
define('DEFAULT_CHECK_IN_TIME', '14:00');
define('DEFAULT_CHECK_OUT_TIME', '11:00');
