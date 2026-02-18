<?php
/**
 * FileManager Class
 * 
 * Handles file operations for logs, exports, and backups.
 */

namespace HotelMaster\Utils;

class FileManager {
    
    /**
     * Save file
     */
    public static function save(string $path, string $content, bool $append = false): bool {
        try {
            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            $flags = $append ? FILE_APPEND | LOCK_EX : LOCK_EX;
            return (bool)file_put_contents($path, $content, $flags);
        } catch (\Exception $e) {
            \HotelMaster\Core\Logger::error("File save failed: {$e->getMessage()}");
            return false;
        }
    }
    
    /**
     * Read file
     */
    public static function read(string $path): ?string {
        try {
            if (!file_exists($path)) {
                return null;
            }
            return file_get_contents($path);
        } catch (\Exception $e) {
            \HotelMaster\Core\Logger::error("File read failed: {$e->getMessage()}");
            return null;
        }
    }
    
    /**
     * Delete file
     */
    public static function delete(string $path): bool {
        try {
            if (file_exists($path)) {
                return unlink($path);
            }
            return true;
        } catch (\Exception $e) {
            \HotelMaster\Core\Logger::error("File delete failed: {$e->getMessage()}");
            return false;
        }
    }
    
    /**
     * Copy file
     */
    public static function copy(string $from, string $to): bool {
        try {
            $dir = dirname($to);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            return copy($from, $to);
        } catch (\Exception $e) {
            \HotelMaster\Core\Logger::error("File copy failed: {$e->getMessage()}");
            return false;
        }
    }
    
    /**
     * Move file
     */
    public static function move(string $from, string $to): bool {
        try {
            $dir = dirname($to);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            return rename($from, $to);
        } catch (\Exception $e) {
            \HotelMaster\Core\Logger::error("File move failed: {$e->getMessage()}");
            return false;
        }
    }
    
    /**
     * Get size of file
     */
    public static function size(string $path): int {
        try {
            if (!file_exists($path)) {
                return 0;
            }
            return filesize($path);
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Check if file exists
     */
    public static function exists(string $path): bool {
        return file_exists($path);
    }
    
    /**
     * List files in directory
     */
    public static function listFiles(string $directory): array {
        try {
            if (!is_dir($directory)) {
                return [];
            }
            
            $files = [];
            foreach (scandir($directory) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                
                $path = $directory . '/' . $file;
                if (is_file($path)) {
                    $files[] = [
                        'name' => $file,
                        'path' => $path,
                        'size' => filesize($path),
                        'modified' => filemtime($path)
                    ];
                }
            }
            
            return $files;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Create backup of database
     */
    public static function backupDatabase(?string $customName = null): ?string {
        try {
            if (!file_exists(DATABASE_PATH)) {
                throw new \Exception('Database file not found');
            }
            
            $backupDir = BACKUPS_PATH;
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $timestamp = date('Y-m-d_H-i-s');
            $filename = $customName ?? "hotel_{$timestamp}.db";
            $backupPath = $backupDir . '/' . $filename;
            
            if (!copy(DATABASE_PATH, $backupPath)) {
                throw new \Exception('Failed to copy database');
            }
            
            \HotelMaster\Core\Logger::info("Database backup created: {$filename}");
            return $backupPath;
        } catch (\Exception $e) {
            \HotelMaster\Core\Logger::error("Backup failed: {$e->getMessage()}");
            return null;
        }
    }
    
    /**
     * Restore database from backup
     */
    public static function restoreDatabase(string $backupPath): bool {
        try {
            if (!file_exists($backupPath)) {
                throw new \Exception('Backup file not found');
            }
            
            // Create safety backup first
            self::backupDatabase("pre_restore_" . date('Y-m-d_H-i-s') . ".db");
            
            if (!copy($backupPath, DATABASE_PATH)) {
                throw new \Exception('Failed to restore database');
            }
            
            // Reset database connection
            \HotelMaster\Core\Database::getInstance();
            
            \HotelMaster\Core\Logger::info("Database restored from: {$backupPath}");
            return true;
        } catch (\Exception $e) {
            \HotelMaster\Core\Logger::error("Restore failed: {$e->getMessage()}");
            return false;
        }
    }
    
    /**
     * Clean old backup files
     */
    public static function cleanOldBackups(int $retentionDays = 30): int {
        try {
            $backupDir = BACKUPS_PATH;
            if (!is_dir($backupDir)) {
                return 0;
            }
            
            $deleted = 0;
            $cutoffTime = time() - ($retentionDays * 24 * 3600);
            
            foreach (scandir($backupDir) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                
                $path = $backupDir . '/' . $file;
                if (is_file($path) && filemtime($path) < $cutoffTime) {
                    unlink($path);
                    $deleted++;
                }
            }
            
            if ($deleted > 0) {
                \HotelMaster\Core\Logger::info("Deleted {$deleted} old backup files");
            }
            
            return $deleted;
        } catch (\Exception $e) {
            \HotelMaster\Core\Logger::error("Backup cleanup failed: {$e->getMessage()}");
            return 0;
        }
    }
}
