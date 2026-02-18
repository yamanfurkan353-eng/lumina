<?php
/**
 * Response Helper Class
 * 
 * Provides standardized response formatting for API endpoints.
 */

namespace HotelMaster\Core;

class Response {
    
    /**
     * Success response
     */
    public static function success(mixed $data = null, string $message = 'Başarılı', int $statusCode = 200): array {
        return [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'status_code' => $statusCode,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Error response
     */
    public static function error(string $message = 'Bir hata oluştu', int $statusCode = 400, ?array $errors = null): array {
        $response = [
            'status' => 'error',
            'message' => $message,
            'data' => null,
            'status_code' => $statusCode,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        return $response;
    }
    
    /**
     * Paginated response
     */
    public static function paginated(array $items, int $total, int $page, int $perPage, string $message = 'Başarılı'): array {
        $lastPage = ceil($total / $perPage);
        
        return [
            'status' => 'success',
            'message' => $message,
            'data' => $items,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
                'has_next' => $page < $lastPage,
                'has_prev' => $page > 1
            ],
            'status_code' => 200,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Created response (201)
     */
    public static function created(mixed $data, string $message = 'Başarıyla oluşturuldu'): array {
        return self::success($data, $message, 201);
    }
    
    /**
     * No content response (204)
     */
    public static function noContent(): array {
        return [
            'status' => 'success',
            'message' => 'İşlem tamamlandı',
            'data' => null,
            'status_code' => 204,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Unauthorized response (401)
     */
    public static function unauthorized(string $message = 'Yetkisiz erişim'): array {
        return self::error($message, 401);
    }
    
    /**
     * Forbidden response (403)
     */
    public static function forbidden(string $message = 'Bu işlemi gerçekleştirme izniniz yok'): array {
        return self::error($message, 403);
    }
    
    /**
     * Not found response (404)
     */
    public static function notFound(string $message = 'Kaynak bulunamadı'): array {
        return self::error($message, 404);
    }
    
    /**
     * Validation error response (422)
     */
    public static function validationError(array $errors, string $message = 'Doğrulama hatası'): array {
        return self::error($message, 422, $errors);
    }
    
    /**
     * Server error response (500)
     */
    public static function serverError(string $message = 'İç sunucu hatası'): array {
        return self::error($message, 500);
    }
    
    /**
     * Send JSON response and exit
     */
    public static function json(array $response, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        exit;
    }
}
