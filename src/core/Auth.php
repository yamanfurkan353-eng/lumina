<?php
/**
 * Authentication Class
 * 
 * Handles user authentication, session management, and permission checking.
 */

namespace HotelMaster\Core;

class Auth {
    
    /**
     * Authenticate user with email and password
     */
    public static function login(string $email, string $password): bool {
        try {
            $db = Database::getInstance();
            
            $user = $db->fetchOne(
                "SELECT id, name, email, password_hash, role, is_active FROM users WHERE email = ? AND is_active = 1",
                [$email]
            );
            
            if (!$user || !password_verify($password, $user['password_hash'])) {
                Logger::info("Failed login attempt for email: {$email}");
                return false;
            }
            
            // Update last login
            $db->execute(
                "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?",
                [$user['id']]
            );
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['last_activity'] = time();
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            
            Logger::audit('login', 'user', $user['id']);
            Logger::info("User logged in: {$email}");
            
            return true;
        } catch (\Exception $e) {
            Logger::error("Login error: {$e->getMessage()}");
            return false;
        }
    }
    
    /**
     * Authenticate user with stored credentials (for API tokens)
     * Reserved for future use
     */
    public static function loginWithToken(string $token): bool {
        // TODO: Implement token-based authentication
        return false;
    }
    
    /**
     * Logout user
     */
    public static function logout(): void {
        if (self::isAuthenticated()) {
            $userId = $_SESSION['user_id'] ?? null;
            Logger::audit('logout', 'user', $userId);
        }
        
        session_destroy();
    }
    
    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated(): bool {
        return isset($_SESSION['user_id']) && self::validateSession();
    }
    
    /**
     * Get current authenticated user
     */
    public static function getCurrentUser(): ?array {
        if (!self::isAuthenticated()) {
            return null;
        }
        
        try {
            $db = Database::getInstance();
            return $db->fetchOne(
                "SELECT id, name, email, role, phone, created_at FROM users WHERE id = ?",
                [$_SESSION['user_id']]
            );
        } catch (\Exception $e) {
            Logger::error("Get current user error: {$e->getMessage()}");
            return null;
        }
    }
    
    /**
     * Get current user ID
     */
    public static function getCurrentUserId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current user role
     */
    public static function getCurrentUserRole(): ?string {
        return $_SESSION['user_role'] ?? null;
    }
    
    /**
     * Check if user has a specific role
     */
    public static function hasRole(string $role): bool {
        return self::isAuthenticated() && $_SESSION['user_role'] === $role;
    }
    
    /**
     * Check if user has a specific permission
     */
    public static function hasPermission(string $permission): bool {
        if (!self::isAuthenticated()) {
            return false;
        }
        
        $roles = require APP_ROOT . '/config/roles.php';
        $userRole = $_SESSION['user_role'];
        
        if (!isset($roles[$userRole])) {
            return false;
        }
        
        return isset($roles[$userRole]['permissions'][$permission]);
    }
    
    /**
     * Check if user is admin
     */
    public static function isAdmin(): bool {
        return self::hasRole(ROLE_ADMIN);
    }
    
    /**
     * Validate session (check timeout, etc.)
     */
    private static function validateSession(): bool {
        // Check session timeout
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
            self::logout();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    /**
     * Get CSRF token for forms
     */
    public static function getCsrfToken(): string {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCsrfToken(string $token): bool {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Require authentication (redirect to login if not authenticated)
     */
    public static function requireAuth(): void {
        if (!self::isAuthenticated()) {
            header('Location: ' . BASE_URL . '/login.html');
            exit;
        }
    }
    
    /**
     * Require specific permission (403 if not permitted)
     */
    public static function requirePermission(string $permission): void {
        if (!self::hasPermission($permission)) {
            http_response_code(403);
            die(json_encode([
                'status' => 'error',
                'message' => 'Bu işlemi gerçekleştirme izniniz yok.'
            ]));
        }
    }
    
    /**
     * Create password hash
     */
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify password against hash
     */
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
}
