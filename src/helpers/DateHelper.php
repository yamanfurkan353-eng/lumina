<?php
/**
 * Helper Functions
 * 
 * Utility functions for date/time, formatting, and common operations.
 */

namespace HotelMaster\Helpers;

/**
 * Format date for display
 */
function formatDate($date, $format = DATE_FORMAT) {
    if (!$date) return '';
    
    try {
        if (is_string($date)) {
            $dateObj = new \DateTime($date);
        } else {
            $dateObj = $date;
        }
        return $dateObj->format($format);
    } catch (\Exception $e) {
        return '';
    }
}

/**
 * Format time for display
 */
function formatTime($time, $format = TIME_FORMAT) {
    if (!$time) return '';
    
    try {
        if (is_string($time)) {
            $timeObj = new \DateTime($time);
        } else {
            $timeObj = $time;
        }
        return $timeObj->format($format);
    } catch (\Exception $e) {
        return '';
    }
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = DATETIME_FORMAT) {
    if (!$datetime) return '';
    
    try {
        if (is_string($datetime)) {
            $dateObj = new \DateTime($datetime);
        } else {
            $dateObj = $datetime;
        }
        return $dateObj->format($format);
    } catch (\Exception $e) {
        return '';
    }
}

/**
 * Format currency (Turkish Lira)
 */
function formatCurrency($amount) {
    if (!is_numeric($amount)) {
        return '';
    }
    
    $formatted = number_format((float)$amount, 2, DECIMAL_SEPARATOR, THOUSANDS_SEPARATOR);
    return $formatted . ' ' . CURRENCY_SYMBOL;
}

/**
 * Format currency as plain text (for JSON)
 */
function formatCurrencyPlain($amount) {
    if (!is_numeric($amount)) {
        return 0;
    }
    
    return round((float)$amount, 2);
}

/**
 * Calculate number of nights between two dates
 */
function calculateNights($checkIn, $checkOut) {
    try {
        if (is_string($checkIn)) {
            $checkInDate = new \DateTime($checkIn);
        } else {
            $checkInDate = $checkIn;
        }
        
        if (is_string($checkOut)) {
            $checkOutDate = new \DateTime($checkOut);
        } else {
            $checkOutDate = $checkOut;
        }
        
        $interval = $checkInDate->diff($checkOutDate);
        return $interval->days;
    } catch (\Exception $e) {
        return 0;
    }
}

/**
 * Calculate reservation total price
 */
function calculateReservationPrice($pricePerNight, $checkIn, $checkOut) {
    $nights = calculateNights($checkIn, $checkOut);
    return $nights * $pricePerNight;
}

/**
 * Get room status display text
 */
function getRoomStatusLabel($status) {
    $statuses = ROOM_STATUSES;
    return $statuses[$status] ?? $status;
}

/**
 * Get reservation status display text
 */
function getReservationStatusLabel($status) {
    $statuses = RESERVATION_STATUSES;
    return $statuses[$status] ?? $status;
}

/**
 * Get payment status display text
 */
function getPaymentStatusLabel($status) {
    $statuses = PAYMENT_STATUSES;
    return $statuses[$status] ?? $status;
}

/**
 * Get user role display text
 */
function getUserRoleLabel($role) {
    $roles = USER_ROLES;
    return $roles[$role] ?? $role;
}

/**
 * Get room type display text
 */
function getRoomTypeLabel($type) {
    $types = ROOM_TYPES;
    return $types[$type] ?? $type;
}

/**
 * Escape HTML for safe output
 */
function escape($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Get today's date as string
 */
function today() {
    return date('Y-m-d');
}

/**
 * Get tomorrow's date as string
 */
function tomorrow() {
    return date('Y-m-d', strtotime('+1 day'));
}

/**
 * Add days to date
 */
function addDays($date, $days) {
    try {
        $dateObj = new \DateTime($date);
        $dateObj->modify("+{$days} days");
        return $dateObj->format('Y-m-d');
    } catch (\Exception $e) {
        return $date;
    }
}

/**
 * Subtract days from date
 */
function subDays($date, $days) {
    try {
        $dateObj = new \DateTime($date);
        $dateObj->modify("-{$days} days");
        return $dateObj->format('Y-m-d');
    } catch (\Exception $e) {
        return $date;
    }
}

/**
 * Check if date is in the past
 */
function isPast($date) {
    try {
        $dateObj = new \DateTime($date);
        return $dateObj < new \DateTime();
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * Check if date is in the future
 */
function isFuture($date) {
    try {
        $dateObj = new \DateTime($date);
        return $dateObj > new \DateTime();
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * Generate random string
 */
function randomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Convert phone number to standard format
 */
function formatPhoneNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    if (strlen($phone) === 10) {
        return '0' . $phone;
    }
    
    if (strlen($phone) === 11 && $phone[0] === '0') {
        return $phone;
    }
    
    if (strlen($phone) === 12 && substr($phone, 0, 2) === '90') {
        return '0' . substr($phone, 2);
    }
    
    return $phone;
}

/**
 * Get pagination offset
 */
function getPaginationOffset($page, $perPage) {
    return ($page - 1) * $perPage;
}

/**
 * Truncate text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}
