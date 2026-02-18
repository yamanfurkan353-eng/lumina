<?php
/**
 * Customer Model
 */

namespace HotelMaster\Models;

use HotelMaster\Core\Database;
use HotelMaster\Utils\Validator;

class Customer {
    protected static string $table = 'customers';
    
    /**
     * Find customer by ID
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
     * Find customer by email
     */
    public static function findByEmail(string $email): ?array {
        try {
            $db = Database::getInstance();
            return $db->fetchOne("SELECT * FROM " . self::$table . " WHERE email = ?", [$email]);
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Find customer by phone
     */
    public static function findByPhone(string $phone): ?array {
        try {
            $db = Database::getInstance();
            return $db->fetchOne("SELECT * FROM " . self::$table . " WHERE phone = ?", [$phone]);
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Get all customers
     */
    public static function all(int $limit = 0, int $offset = 0): array {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM " . self::$table . " ORDER BY created_at DESC";
            
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
     * Search customers
     */
    public static function search(string $query): array {
        try {
            $db = Database::getInstance();
            $searchTerm = "%{$query}%";
            
            return $db->query(
                "SELECT * FROM " . self::$table . " 
                 WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?
                 ORDER BY first_name, last_name",
                [$searchTerm, $searchTerm, $searchTerm, $searchTerm]
            );
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Count total customers
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
     * Create customer
     */
    public static function create(array $data): ?int {
        try {
            $validator = new Validator($data);
            $validator->required('first_name')
                     ->required('last_name')
                     ->required('phone');
            
            if (isset($data['email']) && !empty($data['email'])) {
                $validator->email('email');
            }
            
            if ($validator->fails()) {
                return null;
            }
            
            $db = Database::getInstance();
            
            $sql = "INSERT INTO " . self::$table . " 
                    (first_name, last_name, email, phone, national_id, address, city, country, birth_date, notes, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            
            $db->execute($sql, [
                $data['first_name'],
                $data['last_name'],
                $data['email'] ?? null,
                $data['phone'],
                $data['national_id'] ?? null,
                $data['address'] ?? null,
                $data['city'] ?? null,
                $data['country'] ?? 'Türkiye',
                $data['birth_date'] ?? null,
                $data['notes'] ?? null
            ]);
            
            return $db->lastInsertId();
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Update customer
     */
    public static function update(int $id, array $data): bool {
        try {
            $db = Database::getInstance();
            
            $updates = [];
            $params = [];
            
            $allowedFields = ['first_name', 'last_name', 'email', 'phone', 'national_id', 'address', 'city', 'country', 'birth_date', 'notes'];
            
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
     * Delete customer
     */
    public static function delete(int $id): bool {
        try {
            $db = Database::getInstance();
            return $db->execute("DELETE FROM " . self::$table . " WHERE id = ?", [$id]) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Update customer statistics
     */
    public static function updateStats(int $customerId, float $totalSpent): bool {
        try {
            $db = Database::getInstance();
            
            return $db->execute(
                "UPDATE " . self::$table . " 
                 SET total_stays = total_stays + 1, total_spent = total_spent + ? 
                 WHERE id = ?",
                [$totalSpent, $customerId]
            ) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get customer stay history
     */
    public static function getStayHistory(int $customerId): array {
        try {
            $db = Database::getInstance();
            
            return $db->query(
                "SELECT r.*, room.room_number, room.room_type 
                 FROM reservations r
                 JOIN rooms room ON r.room_id = room.id
                 WHERE r.customer_id = ? AND r.status IN ('checked_out', 'cancelled')
                 ORDER BY r.check_out DESC",
                [$customerId]
            );
        } catch (\Exception $e) {
            return [];
        }
    }
}
