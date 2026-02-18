<?php
/**
 * User Model
 */

namespace HotelMaster\Models;

use HotelMaster\Core\Database;
use HotelMaster\Core\Auth;
use HotelMaster\Utils\Validator;

class User {
    protected static string $table = 'users';
    
    /**
     * Find user by ID
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
     * Find user by email
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
     * Get all users
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
     * Get users by role
     */
    public static function byRole(string $role): array {
        try {
            $db = Database::getInstance();
            return $db->query(
                "SELECT * FROM " . self::$table . " WHERE role = ? AND is_active = 1 ORDER BY name",
                [$role]
            );
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Count total users
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
     * Create new user
     */
    public static function create(array $data): ?int {
        try {
            // Validate
            $validator = new Validator($data);
            $validator->required('name')
                     ->required('email')
                     ->required('password')
                     ->required('role')
                     ->email('email')
                     ->unique('email', self::$table, 'Bu e-posta adresi zaten kullanılıyor')
                     ->in('role', ['admin', 'receptionist', 'housekeeping']);
            
            if ($validator->fails()) {
                return null;
            }
            
            $db = Database::getInstance();
            $passwordHash = Auth::hashPassword($data['password']);
            
            $sql = "INSERT INTO " . self::$table . " (name, email, password_hash, role, phone, is_active, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            
            $db->execute($sql, [
                $data['name'],
                $data['email'],
                $passwordHash,
                $data['role'],
                $data['phone'] ?? null,
                $data['is_active'] ?? 1
            ]);
            
            return $db->lastInsertId();
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Update user
     */
    public static function update(int $id, array $data): bool {
        try {
            $db = Database::getInstance();
            
            $updates = [];
            $params = [];
            
            if (isset($data['name'])) {
                $updates[] = 'name = ?';
                $params[] = $data['name'];
            }
            
            if (isset($data['email'])) {
                $updates[] = 'email = ?';
                $params[] = $data['email'];
            }
            
            if (isset($data['password'])) {
                $updates[] = 'password_hash = ?';
                $params[] = Auth::hashPassword($data['password']);
            }
            
            if (isset($data['role'])) {
                $updates[] = 'role = ?';
                $params[] = $data['role'];
            }
            
            if (isset($data['phone'])) {
                $updates[] = 'phone = ?';
                $params[] = $data['phone'];
            }
            
            if (isset($data['is_active'])) {
                $updates[] = 'is_active = ?';
                $params[] = $data['is_active'];
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
     * Delete user
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
     * Soft delete (deactivate) user
     */
    public static function deactivate(int $id): bool {
        return self::update($id, ['is_active' => 0]);
    }
    
    /**
     * Activate user
     */
    public static function activate(int $id): bool {
        return self::update($id, ['is_active' => 1]);
    }
}
