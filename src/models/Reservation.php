<?php
/**
 * Reservation Model
 */

namespace HotelMaster\Models;

use HotelMaster\Core\Database;
use HotelMaster\Utils\Validator;

class Reservation {
    protected static string $table = 'reservations';
    
    /**
     * Find reservation by ID
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
     * Get all reservations
     */
    public static function all(int $limit = 0, int $offset = 0): array {
        try {
            $db = Database::getInstance();
            $sql = "SELECT r.*, c.first_name, c.last_name, c.phone, room.room_number 
                    FROM " . self::$table . " r
                    JOIN customers c ON r.customer_id = c.id
                    JOIN rooms room ON r.room_id = room.id
                    ORDER BY r.check_in DESC";
            
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
     * Get reservations for date range
     */
    public static function byDateRange(string $from, string $to): array {
        try {
            $db = Database::getInstance();
            
            return $db->query(
                "SELECT r.*, c.first_name, c.last_name, room.room_number 
                 FROM " . self::$table . " r
                 JOIN customers c ON r.customer_id = c.id
                 JOIN rooms room ON r.room_id = room.id
                 WHERE r.check_in >= ? AND r.check_out <= ? AND r.status IN ('confirmed', 'checked_in')
                 ORDER BY r.check_in",
                [$from, $to]
            );
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get upcoming reservations
     */
    public static function upcoming(int $daysAhead = 30): array {
        try {
            $db = Database::getInstance();
            $today = date('Y-m-d');
            $futureDate = date('Y-m-d', strtotime("+{$daysAhead} days"));
            
            return $db->query(
                "SELECT r.*, c.first_name, c.last_name, c.phone, room.room_number 
                 FROM " . self::$table . " r
                 JOIN customers c ON r.customer_id = c.id
                 JOIN rooms room ON r.room_id = room.id
                 WHERE r.check_in BETWEEN ? AND ? AND r.status IN ('confirmed', 'checked_in')
                 ORDER BY r.check_in",
                [$today, $futureDate]
            );
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get today's check-ins
     */
    public static function todayCheckIns(): array {
        try {
            $db = Database::getInstance();
            $today = date('Y-m-d');
            
            return $db->query(
                "SELECT r.*, c.first_name, c.last_name, c.phone, room.room_number 
                 FROM " . self::$table . " r
                 JOIN customers c ON r.customer_id = c.id
                 JOIN rooms room ON r.room_id = room.id
                 WHERE r.check_in = ? AND r.status = 'confirmed'
                 ORDER BY r.check_in",
                [$today]
            );
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get today's check-outs
     */
    public static function todayCheckOuts(): array {
        try {
            $db = Database::getInstance();
            $today = date('Y-m-d');
            
            return $db->query(
                "SELECT r.*, c.first_name, c.last_name, c.phone, room.room_number 
                 FROM " . self::$table . " r
                 JOIN customers c ON r.customer_id = c.id
                 JOIN rooms room ON r.room_id = room.id
                 WHERE r.check_out = ? AND r.status = 'checked_in'
                 ORDER BY r.check_out",
                [$today]
            );
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Count total reservations
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
     * Count confirmed reservations
     */
    public static function countConfirmed(): int {
        try {
            $db = Database::getInstance();
            $result = $db->fetchOne(
                "SELECT COUNT(*) as count FROM " . self::$table . " WHERE status = 'confirmed'"
            );
            return $result['count'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get revenue statistics
     */
    public static function revenueStats(string $from, string $to): array {
        try {
            $db = Database::getInstance();
            
            $result = $db->fetchOne(
                "SELECT 
                 COUNT(*) as total_reservations,
                 SUM(total_price) as total_revenue,
                 AVG(total_price) as avg_price
                 FROM " . self::$table . " 
                 WHERE check_out BETWEEN ? AND ? AND status IN ('checked_out', 'confirmed')",
                [$from, $to]
            );
            
            return $result ?: ['total_reservations' => 0, 'total_revenue' => 0, 'avg_price' => 0];
        } catch (\Exception $e) {
            return ['total_reservations' => 0, 'total_revenue' => 0, 'avg_price' => 0];
        }
    }
    
    /**
     * Create reservation
     */
    public static function create(array $data): ?int {
        try {
            $validator = new Validator($data);
            $validator->required('customer_id')
                     ->required('room_id')
                     ->required('check_in')
                     ->required('check_out')
                     ->required('number_of_guests')
                     ->required('created_by')
                     ->numeric('customer_id')
                     ->numeric('room_id')
                     ->numeric('created_by');
            
            if ($validator->fails()) {
                return null;
            }
            
            $db = Database::getInstance();
            
            // Calculate total price if room rate provided
            $totalPrice = null;
            if (isset($data['total_price'])) {
                $totalPrice = $data['total_price'];
            }
            
            $sql = "INSERT INTO " . self::$table . " 
                    (customer_id, room_id, check_in, check_out, number_of_guests, total_price, status, payment_status, notes, created_by, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            
            $db->execute($sql, [
                $data['customer_id'],
                $data['room_id'],
                $data['check_in'],
                $data['check_out'],
                $data['number_of_guests'],
                $totalPrice,
                $data['status'] ?? 'confirmed',
                $data['payment_status'] ?? 'pending',
                $data['notes'] ?? null,
                $data['created_by']
            ]);
            
            return $db->lastInsertId();
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Update reservation
     */
    public static function update(int $id, array $data): bool {
        try {
            $db = Database::getInstance();
            
            $updates = [];
            $params = [];
            
            $allowedFields = ['check_in', 'check_out', 'number_of_guests', 'total_price', 'status', 'payment_status', 'notes'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updates[] = $field . ' = ?';
                    $params[] = $data[$field];
                }
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
     * Change reservation status
     */
    public static function changeStatus(int $id, string $status): bool {
        return self::update($id, ['status' => $status]);
    }
    
    /**
     * Update payment status
     */
    public static function updatePaymentStatus(int $id, string $paymentStatus): bool {
        return self::update($id, ['payment_status' => $paymentStatus]);
    }
    
    /**
     * Cancel reservation
     */
    public static function cancel(int $id): bool {
        return self::update($id, ['status' => 'cancelled']);
    }
    
    /**
     * Check in reservation
     */
    public static function checkIn(int $id): bool {
        return self::update($id, ['status' => 'checked_in']);
    }
    
    /**
     * Check out reservation
     */
    public static function checkOut(int $id, ?float $totalPrice = null): bool {
        $data = ['status' => 'checked_out'];
        if ($totalPrice !== null) {
            $data['total_price'] = $totalPrice;
        }
        return self::update($id, $data);
    }
    
    /**
     * Delete reservation
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
