<?php
/**
 * Validator Class
 * 
 * Input validation and sanitization for forms and API requests.
 */

namespace HotelMaster\Utils;

class Validator {
    private array $errors = [];
    private array $data = [];
    
    public function __construct(array $data = []) {
        $this->data = $data;
    }
    
    /**
     * Set data to validate
     */
    public function setData(array $data): self {
        $this->data = $data;
        return $this;
    }
    
    /**
     * Validate required field
     */
    public function required(string $field, string $message = ''): self {
        $message = $message ?: "{$field} alanı zorunludur";
        
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field] = $message;
        }
        
        return $this;
    }
    
    /**
     * Validate email
     */
    public function email(string $field, string $message = ''): self {
        $message = $message ?: "{$field} geçerli bir e-posta adresi değil";
        
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message;
        }
        
        return $this;
    }
    
    /**
     * Validate minimum length
     */
    public function minLength(string $field, int $min, string $message = ''): self {
        $message = $message ?: "{$field} en az {$min} karakter olmalıdır";
        
        if (isset($this->data[$field]) && strlen((string)$this->data[$field]) < $min) {
            $this->errors[$field] = $message;
        }
        
        return $this;
    }
    
    /**
     * Validate maximum length
     */
    public function maxLength(string $field, int $max, string $message = ''): self {
        $message = $message ?: "{$field} maksimum {$max} karakter olmalıdır";
        
        if (isset($this->data[$field]) && strlen((string)$this->data[$field]) > $max) {
            $this->errors[$field] = $message;
        }
        
        return $this;
    }
    
    /**
     * Validate numeric
     */
    public function numeric(string $field, string $message = ''): self {
        $message = $message ?: "{$field} bir sayı olmalıdır";
        
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = $message;
        }
        
        return $this;
    }
    
    /**
     * Validate integer
     */
    public function integer(string $field, string $message = ''): self {
        $message = $message ?: "{$field} bir tam sayı olmalıdır";
        
        if (isset($this->data[$field]) && !is_numeric($this->data[$field]) || floor($this->data[$field]) != $this->data[$field]) {
            $this->errors[$field] = $message;
        }
        
        return $this;
    }
    
    /**
     * Validate date format (Y-m-d)
     */
    public function date(string $field, string $format = 'Y-m-d', string $message = ''): self {
        $message = $message ?: "{$field} geçerli bir tarih değil";
        
        if (isset($this->data[$field])) {
            $date = \DateTime::createFromFormat($format, $this->data[$field]);
            if (!$date || $date->format($format) !== $this->data[$field]) {
                $this->errors[$field] = $message;
            }
        }
        
        return $this;
    }
    
    /**
     * Validate date is in future
     */
    public function futureDate(string $field, string $message = ''): self {
        $message = $message ?: "{$field} gelecek bir tarih olmalıdır";
        
        if (isset($this->data[$field])) {
            $date = new \DateTime($this->data[$field]);
            if ($date <= new \DateTime()) {
                $this->errors[$field] = $message;
            }
        }
        
        return $this;
    }
    
    /**
     * Validate value is in array
     */
    public function in(string $field, array $values, string $message = ''): self {
        $message = $message ?: "{$field} geçerli bir değer değil";
        
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values, true)) {
            $this->errors[$field] = $message;
        }
        
        return $this;
    }
    
    /**
     * Validate unique in database
     */
    public function unique(string $field, string $table, string $message = ''): self {
        $message = $message ?: "{$field} zaten kullanılıyor";
        
        if (!isset($this->data[$field])) {
            return $this;
        }
        
        try {
            $db = \HotelMaster\Core\Database::getInstance();
            $result = $db->fetchOne(
                "SELECT id FROM {$table} WHERE {$field} = ?",
                [$this->data[$field]]
            );
            
            if ($result) {
                $this->errors[$field] = $message;
            }
        } catch (\Exception $e) {
            \HotelMaster\Core\Logger::error("Unique validation error: {$e->getMessage()}");
        }
        
        return $this;
    }
    
    /**
     * Validate phone number (Turkish format)
     */
    public function phone(string $field, string $message = ''): self {
        $message = $message ?: "{$field} geçerli bir telefon numarası değil";
        
        if (isset($this->data[$field])) {
            $phone = preg_replace('/[^0-9]/', '', $this->data[$field]);
            if (strlen($phone) < 10 || strlen($phone) > 13) {
                $this->errors[$field] = $message;
            }
        }
        
        return $this;
    }
    
    /**
     * Check if validation passed
     */
    public function passes(): bool {
        return empty($this->errors);
    }
    
    /**
     * Check if validation failed
     */
    public function fails(): bool {
        return !$this->passes();
    }
    
    /**
     * Get errors
     */
    public function errors(): array {
        return $this->errors;
    }
    
    /**
     * Get error message for field
     */
    public function error(string $field): ?string {
        return $this->errors[$field] ?? null;
    }
    
    /**
     * Get first error message
     */
    public function firstError(): ?string {
        return reset($this->errors) ?: null;
    }
    
    /**
     * Clear all errors
     */
    public function clearErrors(): self {
        $this->errors = [];
        return $this;
    }
}
