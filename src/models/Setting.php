<?php
/**
 * Setting Model
 */

namespace HotelMaster\Models;

use HotelMaster\Core\Database;

class Setting {
    protected static string $table = 'settings';
    
    /**
     * Get setting value
     */
    public static function get(string $key, mixed $default = null): mixed {
        try {
            $db = Database::getInstance();
            $result = $db->fetchOne("SELECT value, type FROM " . self::$table . " WHERE key = ?", [$key]);
            
            if (!$result) {
                return $default;
            }
            
            return self::castValue($result['value'], $result['type']);
        } catch (\Exception $e) {
            return $default;
        }
    }
    
    /**
     * Set setting value
     */
    public static function set(string $key, mixed $value, string $type = 'string'): bool {
        try {
            $db = Database::getInstance();
            $stringValue = self::stringifyValue($value);
            
            // Try to update first
            $updated = $db->execute(
                "UPDATE " . self::$table . " SET value = ?, type = ?, updated_at = CURRENT_TIMESTAMP WHERE key = ?",
                [$stringValue, $type, $key]
            );
            
            // If no rows updated, insert
            if ($updated === 0) {
                $db->execute(
                    "INSERT INTO " . self::$table . " (key, value, type, updated_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)",
                    [$key, $stringValue, $type]
                );
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get all settings
     */
    public static function all(): array {
        try {
            $db = Database::getInstance();
            $results = $db->query("SELECT * FROM " . self::$table);
            
            $settings = [];
            foreach ($results as $result) {
                $settings[$result['key']] = self::castValue($result['value'], $result['type']);
            }
            
            return $settings;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Delete setting
     */
    public static function delete(string $key): bool {
        try {
            $db = Database::getInstance();
            return $db->execute("DELETE FROM " . self::$table . " WHERE key = ?", [$key]) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Cast value to proper type
     */
    private static function castValue(string $value, string $type): mixed {
        return match($type) {
            'int' => (int)$value,
            'bool' => (bool)filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value
        };
    }
    
    /**
     * Convert value to string for storage
     */
    private static function stringifyValue(mixed $value): string {
        return match(gettype($value)) {
            'boolean' => $value ? '1' : '0',
            'array', 'object' => json_encode($value),
            default => (string)$value
        };
    }
    
    /**
     * Get hotel name
     */
    public static function getHotelName(): string {
        return self::get('hotel_name', 'Hotel Master Lite');
    }
    
    /**
     * Get check-in time
     */
    public static function getCheckInTime(): string {
        return self::get('check_in_time', DEFAULT_CHECK_IN_TIME);
    }
    
    /**
     * Get check-out time
     */
    public static function getCheckOutTime(): string {
        return self::get('check_out_time', DEFAULT_CHECK_OUT_TIME);
    }
}
