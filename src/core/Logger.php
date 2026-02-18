<?php
/**
 * Logger Class
 * 
 * Handles application logging for errors, access, and user activities.
 */

namespace HotelMaster\Core;

class Logger {
    
    /**
     * Log an error
     */
    public static function error(string $message, array $context = []): void {
        self::log('error', $message, $context, LOGS_PATH . '/error.log');
    }
    
    /**
     * Log an info message
     */
    public static function info(string $message, array $context = []): void {
        self::log('info', $message, $context, LOGS_PATH . '/access.log');
    }
    
    /**
     * Log debug message
     */
    public static function debug(string $message, array $context = []): void {
        if (DEBUG_MODE) {
            self::log('debug', $message, $context, LOGS_PATH . '/debug.log');
        }
    }
    
    /**
     * Log user activity/audit
     */
    public static function audit(string $action, string $entityType, ?int $entityId = null, ?array $oldValues = null, ?array $newValues = null): void {
        try {
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            $ipAddress = self::getClientIp();
            
            $sql = "INSERT INTO audit_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $db = Database::getInstance();
            $db->execute($sql, [
                $userId,
                $action,
                $entityType,
                $entityId,
                $oldValues ? json_encode($oldValues) : null,
                $newValues ? json_encode($newValues) : null,
                $ipAddress
            ]);
        } catch (\Exception $e) {
            self::error("Audit logging failed: {$e->getMessage()}");
        }
    }
    
    /**
     * Write log to file
     */
    private static function log(string $level, string $message, array $context = [], string $logFile = ''): void {
        try {
            // Ensure log directory exists
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            $timestamp = date('Y-m-d H:i:s');
            $contextStr = !empty($context) ? ' | ' . json_encode($context) : '';
            $logMessage = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
            
            // Rotate log if size exceeds 10MB
            if (file_exists($logFile) && filesize($logFile) > 10 * 1024 * 1024) {
                self::rotateLog($logFile);
            }
            
            file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        } catch (\Exception $e) {
            // Silently fail if logging fails to prevent infinite loops
        }
    }
    
    /**
     * Rotate log file
     */
    private static function rotateLog(string $logFile): void {
        $timestamp = date('Y-m-d_H-i-s');
        $rotatedFile = $logFile . '.' . $timestamp . '.bak';
        
        if (file_exists($logFile)) {
            rename($logFile, $rotatedFile);
        }
        
        // Clean up old backup files (keep last 10)
        $pattern = $logFile . '.*.bak';
        $files = glob($pattern);
        if (count($files) > 10) {
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            for ($i = 0; $i < count($files) - 10; $i++) {
                unlink($files[$i]);
            }
        }
    }
    
    /**
     * Get client IP address
     */
    private static function getClientIp(): string {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        }
        
        return trim($ip);
    }
}
