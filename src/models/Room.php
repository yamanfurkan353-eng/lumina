<?php
/**
 * Room Model
 */

namespace HotelMaster\Models;

use HotelMaster\Core\Database;
use HotelMaster\Utils\Validator;

class Room {
    protected static string $table = 'rooms';
    
    /**
     * Find room by ID
     */
    public static function find(int $id): ?array {
        try {
            $db = Database::getInstance();
            return $db->fetchOne("SELECT * FROM " . self::$table . " WHERE id = ?", [$id]);
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Find room by room number
     */
    public static function findByNumber(string $roomNumber): ?array {
        try {
            $db = Database::getInstance();
            return $db->fetchOne("SELECT * FROM " . self::$table . " WHERE room_number = ?", [$roomNumber]);
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Get all rooms
     */
    public static function all(int $limit = 0, int $offset = 0): array {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM " . self::$table . " ORDER BY floor, room_number";
            
            if ($limit > 0) {
                $sql .= " LIMIT ? OFFSET ?";
                return $db->query($sql, [$limit, $offset]);
            }
            
            return $db->query($sql);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get available rooms for date range
     */
    public static function available(string $checkIn, string $checkOut): array {
        try {
            $db = Database::getInstance();
            
            $sql = "SELECT r.* FROM " . self::$table . " r
                    WHERE r.status = 'available'
                    AND r.id NOT IN (
                        SELECT room_id FROM reservations 
                        WHERE status IN ('confirmed', 'checked_in')
                        AND NOT (check_out <= ? OR check_in >= ?)
                    )
                    ORDER BY r.floor, r.room_number";
            
            return $db->query($sql, [$checkIn, $checkOut]);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get rooms by status
     */
    public static function byStatus(string $status): array {
        try {
            $db = Database::getInstance();
            return $db->query(
                "SELECT * FROM " . self::$table . " WHERE status = ? ORDER BY floor, room_number",
                [$status]
            );
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get rooms by type
     */
    public static function byType(string $type): array {
        try {
            $db = Database::getInstance();
            return $db->query(
                "SELECT * FROM " . self::$table . " WHERE room_type = ? ORDER BY floor, room_number",
                [$type]
            );
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Count total rooms
     */
    public static function count(): int {
        try {
            $db = Database::getInstance();
            $result = $db->fetchOne("SELECT COUNT(*) as count FROM " . self::$table);
            return $result['count'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Count occupied rooms
     */
    public static function countOccupied(): int {
        try {
            $db = Database::getInstance();
            $result = $db->fetchOne(
                "SELECT COUNT(*) as count FROM " . self::$table . " WHERE status = 'occupied'"
            );
            return $result['count'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Count available rooms
     */
    public static function countAvailable(): int {
        try {
            $db = Database::getInstance();
            $result = $db->fetchOne(
                "SELECT COUNT(*) as count FROM " . self::$table . " WHERE status = 'available'"
            );
            return $result['count'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Occupancy rate
     */
    public static function occupancyRate(): float {
        $total = self::count();
        if ($total === 0) {
            return 0;
        }
        
        $occupied = self::countOccupied();
        return ($occupied / $total) * 100;
    }
    
    /**
     * Create room
     */
    public static function create(array $data): ?int {
        try {
            $validator = new Validator($data);
            $validator->required('room_number')
                     ->required('room_type')
                     ->required('price_per_night')
                     ->numeric('price_per_night')
                     ->unique('room_number', self::$table, 'Bu oda numarası zaten kullanılıyor');
            
            if ($validator->fails()) {
                return null;
            }
            
            $db = Database::getInstance();
            
            $sql = "INSERT INTO " . self::$table . " 
                    (room_number, room_type, capacity, price_per_night, status, floor, amenities, notes, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            
            $db->execute($sql, [
                $data['room_number'],
                $data['room_type'],
                $data['capacity'] ?? 2,
                $data['price_per_night'],
                $data['status'] ?? 'available',
                $data['floor'] ?? 1,
                isset($data['amenities']) ? json_encode($data['amenities']) : null,
                $data['notes'] ?? null
            ]);
            
            return $db->lastInsertId();
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Update room
     */
    public static function update(int $id, array $data): bool {
        try {
            $db = Database::getInstance();
            
            $updates = [];
            $params = [];
            
            if (isset($data['room_number'])) {
                $updates[] = 'room_number = ?';
                $params[] = $data['room_number'];
            }
            
            if (isset($data['price_per_night'])) {
                $updates[] = 'price_per_night = ?';
                $params[] = $data['price_per_night'];
            }
            
            if (isset($data['status'])) {
                $updates[] = 'status = ?';
                $params[] = $data['status'];
            }
            
            if (isset($data['notes'])) {
                $updates[] = 'notes = ?';
                $params[] = $data['notes'];
            }
            
            if (isset($data['amenities'])) {
                $updates[] = 'amenities = ?';
                $params[] = is_array($data['amenities']) ? json_encode($data['amenities']) : $data['amenities'];
            }
            
            if (empty($updates)) {
                return true;
            }
            
            $updates[] = 'updated_at = CURRENT_TIMESTAMP';
            $params[] = $id;
            
            $sql = "UPDATE " . self::$table . " SET " . implode(', ', $updates) . " WHERE id = ?";
            return $db->execute($sql, $params) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Change room status
     */
    public static function changeStatus(int $id, string $status): bool {
        return self::update($id, ['status' => $status]);
    }
    
    /**
     * Delete room
     */
    public static function delete(int $id): bool {
        try {
            $db = Database::getInstance();
            return $db->execute("DELETE FROM " . self::$table . " WHERE id = ?", [$id]) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}
