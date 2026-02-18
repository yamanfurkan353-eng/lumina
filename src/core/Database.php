<?php
/**
 * Database Class - Singleton Pattern
 * 
 * Manages SQLite database connections and queries using prepared statements.
 * Ensures single instance per application lifecycle.
 */

namespace HotelMaster\Core;

class Database {
    private static ?self $instance = null;
    private ?\PDO $connection = null;
    private array $lastError = [];
    
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new \Exception('Cannot unserialize a singleton');
    }
    
    /**
     * Connect to SQLite database
     */
    private function connect(): void {
        try {
            $dbPath = DATABASE_PATH;
            $dsn = "sqlite:{$dbPath}";
            
            $this->connection = new \PDO($dsn);
            
            // Enable foreign keys
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->connection->exec('PRAGMA foreign_keys = ON');
            $this->connection->exec('PRAGMA journal_mode = WAL');
            
        } catch (\Exception $e) {
            $this->lastError = [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
            Logger::error('Database connection failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Execute a prepared statement query
     * 
     * @param string $sql SQL query with placeholders (? or :name)
     * @param array $params Parameters to bind
     * @return array Array of result rows
     */
    public function query(string $sql, array $params = []): array {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->lastError = [
                'message' => $e->getMessage(),
                'sql' => $sql,
                'params' => $params
            ];
            Logger::error("Query failed: {$e->getMessage()}", ['sql' => $sql]);
            throw $e;
        }
    }
    
    /**
     * Execute a prepared statement (INSERT, UPDATE, DELETE)
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return int Number of affected rows
     */
    public function execute(string $sql, array $params = []): int {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            $this->lastError = [
                'message' => $e->getMessage(),
                'sql' => $sql,
                'params' => $params
            ];
            Logger::error("Execute failed: {$e->getMessage()}", ['sql' => $sql]);
            throw $e;
        }
    }
    
    /**
     * Get a single row
     * 
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return array|null Single row or null if not found
     */
    public function fetchOne(string $sql, array $params = []): ?array {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException $e) {
            Logger::error("FetchOne failed: {$e->getMessage()}", ['sql' => $sql]);
            throw $e;
        }
    }
    
    /**
     * Get last inserted ID
     */
    public function lastInsertId(): int {
        return (int)$this->connection->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction(): void {
        try {
            $this->connection->beginTransaction();
        } catch (\PDOException $e) {
            Logger::error("Begin transaction failed: {$e->getMessage()}");
            throw $e;
        }
    }
    
    /**
     * Commit transaction
     */
    public function commit(): void {
        try {
            $this->connection->commit();
        } catch (\PDOException $e) {
            Logger::error("Commit failed: {$e->getMessage()}");
            throw $e;
        }
    }
    
    /**
     * Rollback transaction
     */
    public function rollback(): void {
        try {
            $this->connection->rollBack();
        } catch (\PDOException $e) {
            Logger::error("Rollback failed: {$e->getMessage()}");
            throw $e;
        }
    }
    
    /**
     * Check if database is initialized
     */
    public static function isInitialized(): bool {
        return file_exists(DATABASE_PATH);
    }
    
    /**
     * Get last error
     */
    public function getLastError(): array {
        return $this->lastError;
    }
    
    /**
     * Clear last error
     */
    public function clearLastError(): void {
        $this->lastError = [];
    }
    
    /**
     * Get PDO connection directly (for advanced operations)
     */
    public function getConnection(): \PDO {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }
}
