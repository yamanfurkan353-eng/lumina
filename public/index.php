<?php
/**
 * API Entry Point
 * 
 * Single entry point for all API requests. Routes to appropriate controllers.
 */

// Load configuration
require_once dirname(__DIR__) . '/config/config.php';

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'HotelMaster\\';
    
    if (strpos($class, $prefix) === 0) {
        $path = dirname(__DIR__) . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        
        if (file_exists($path)) {
            require_once $path;
        }
    }
});

// Load helpers
require_once APP_ROOT . '/src/helpers/DateHelper.php';

try {
    // Check if database is initialized
    if (!is_api_request() && !file_exists(DATABASE_PATH)) {
        header('Location: /setup.html');
        exit;
    }
    
    // Initialize database (will create connection and verify)
    \HotelMaster\Core\Database::getInstance();
    
    // Setup routes
    $router = new \HotelMaster\Core\Router();
    
    // Authentication Routes
    $router->post('/api/auth/login', 'AuthController', 'login');
    $router->get('/api/auth/user', 'AuthController', 'user');
    $router->post('/api/auth/logout', 'AuthController', 'logout');
    $router->post('/api/auth/change-password', 'AuthController', 'changePassword');
    
    // Dashboard Route
    $router->get('/api/dashboard', 'DashboardController', 'index');
    
    // Room Routes
    $router->get('/api/rooms', 'RoomController', 'list');
    $router->get('/api/rooms/{id}', 'RoomController', 'show');
    $router->post('/api/rooms', 'RoomController', 'store');
    $router->put('/api/rooms/{id}', 'RoomController', 'update');
    $router->put('/api/rooms/{id}/status', 'RoomController', 'changeStatus');
    $router->delete('/api/rooms/{id}', 'RoomController', 'delete');
    $router->get('/api/rooms/available', 'RoomController', 'available');
    
    // Reservation Routes
    $router->get('/api/reservations', 'ReservationController', 'list');
    $router->get('/api/reservations/{id}', 'ReservationController', 'show');
    $router->post('/api/reservations', 'ReservationController', 'store');
    $router->put('/api/reservations/{id}', 'ReservationController', 'update');
    $router->post('/api/reservations/{id}/checkin', 'ReservationController', 'checkIn');
    $router->post('/api/reservations/{id}/checkout', 'ReservationController', 'checkOut');
    $router->delete('/api/reservations/{id}', 'ReservationController', 'cancel');
    $router->get('/api/reservations/upcoming', 'ReservationController', 'upcoming');
    $router->get('/api/reservations/calendar', 'ReservationController', 'calendar');
    
    // Customer Routes
    $router->get('/api/customers', 'CustomerController', 'list');
    $router->get('/api/customers/{id}', 'CustomerController', 'show');
    $router->post('/api/customers', 'CustomerController', 'store');
    $router->put('/api/customers/{id}', 'CustomerController', 'update');
    $router->delete('/api/customers/{id}', 'CustomerController', 'delete');
    $router->get('/api/customers/search', 'CustomerController', 'search');
    
    // Settings Routes
    $router->get('/api/settings', 'SettingsController', 'index');
    $router->put('/api/settings', 'SettingsController', 'update');
    $router->get('/api/settings/{key}', 'SettingsController', 'show');
    $router->post('/api/settings/backup', 'SettingsController', 'backup');
    $router->post('/api/settings/restore', 'SettingsController', 'restore');
    $router->get('/api/settings/backups', 'SettingsController', 'listBackups');
    
    // Export Routes
    $router->get('/api/export/reservations/csv', 'ExportController', 'reservationsCSV');
    $router->get('/api/export/customers/csv', 'ExportController', 'customersCSV');
    $router->get('/api/export/rooms/csv', 'ExportController', 'roomsCSV');
    $router->get('/api/export/reservation/{id}/pdf', 'ExportController', 'generateReservationPDF');
    
    // Charts Routes
    $router->get('/api/charts/revenue', 'ChartsController', 'revenueChart');
    $router->get('/api/charts/occupancy', 'ChartsController', 'occupancyChart');
    $router->get('/api/charts/room-types', 'ChartsController', 'roomTypesChart');
    $router->get('/api/charts/booking-status', 'ChartsController', 'bookingStatusChart');
    
    // Dispatch request
    $router->dispatch();
    
} catch (\PDOException $e) {
    \HotelMaster\Core\Logger::error("PDO Exception: {$e->getMessage()}");
    
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'error',
        'message' => 'Veritabanı bağlantısı kurulamadı',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (\Exception $e) {
    \HotelMaster\Core\Logger::error("Exception: {$e->getMessage()}");
    
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'error',
        'message' => 'Bir hata oluştu',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
