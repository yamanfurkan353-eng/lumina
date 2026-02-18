<?php
/**
 * Reservation Controller
 * 
 * Handles reservation management endpoints
 */

namespace HotelMaster\Controllers;

use HotelMaster\Core\Auth;
use HotelMaster\Core\Response;
use HotelMaster\Core\Logger;
use HotelMaster\Models\Reservation;
use HotelMaster\Models\Room;
use HotelMaster\Models\Customer;
use HotelMaster\Utils\Validator;
use HotelMaster\Helpers;

class ReservationController {
    
    /**
     * GET /api/reservations
     * Get all reservations
     */
    public function list(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('reservations.view');
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = ITEMS_PER_PAGE;
            $offset = ($page - 1) * $perPage;
            
            $reservations = Reservation::all($perPage, $offset);
            $total = Reservation::count();
            
            return Response::paginated($reservations, $total, $page, $perPage, 'Rezervasyonlar başarıyla alındı');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/reservations/{id}
     * Get single reservation
     */
    public function show(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('reservations.view');
            
            $reservation = Reservation::find($params['id']);
            
            if (!$reservation) {
                return Response::notFound('Rezervasyon bulunamadı');
            }
            
            return Response::success($reservation);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * POST /api/reservations
     * Create new reservation
     */
    public function store(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('reservations.create');
            
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            $validator = new Validator($input);
            $validator->required('customer_id')
                     ->required('room_id')
                     ->required('check_in')
                     ->required('check_out')
                     ->required('number_of_guests')
                     ->numeric('customer_id')
                     ->numeric('room_id')
                     ->numeric('number_of_guests');
            
            if ($validator->fails()) {
                return Response::validationError($validator->errors());
            }
            
            // Validate customer exists
            $customer = Customer::find($input['customer_id']);
            if (!$customer) {
                return Response::error('Müşteri bulunamadı', 400);
            }
            
            // Validate room exists
            $room = Room::find($input['room_id']);
            if (!$room) {
                return Response::error('Oda bulunamadı', 400);
            }
            
            // Calculate total price
            $nights = \HotelMaster\Helpers\calculateNights($input['check_in'], $input['check_out']);
            $totalPrice = $nights * $room['price_per_night'];
            
            $input['total_price'] = $totalPrice;
            $input['created_by'] = Auth::getCurrentUserId();
            
            $reservationId = Reservation::create($input);
            
            if (!$reservationId) {
                return Response::error('Rezervasyon oluşturulamadı', 400);
            }
            
            Logger::audit('reservation_created', 'reservation', $reservationId, null, $input);
            
            $reservation = Reservation::find($reservationId);
            return Response::created($reservation, 'Rezervasyon başarılı oluşturuldu');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * PUT /api/reservations/{id}
     * Update reservation
     */
    public function update(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('reservations.edit');
            
            $reservation = Reservation::find($params['id']);
            if (!$reservation) {
                return Response::notFound('Rezervasyon bulunamadı');
            }
            
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            if (Reservation::update($params['id'], $input)) {
                Logger::audit('reservation_updated', 'reservation', $params['id'], $reservation, $input);
                
                $updatedReservation = Reservation::find($params['id']);
                return Response::success($updatedReservation, 'Rezervasyon başarılı güncellendi');
            }
            
            return Response::error('Rezervasyon güncellenemedi', 400);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * POST /api/reservations/{id}/checkin
     * Check in reservation
     */
    public function checkIn(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('reservations.checkin');
            
            $reservation = Reservation::find($params['id']);
            if (!$reservation) {
                return Response::notFound('Rezervasyon bulunamadı');
            }
            
            if ($reservation['status'] !== 'confirmed') {
                return Response::error('Sadece onaylanan rezervasyonlar check-in yapılabilir', 400);
            }
            
            if (Reservation::checkIn($params['id']) && Room::changeStatus($reservation['room_id'], 'occupied')) {
                Logger::audit('reservation_checked_in', 'reservation', $params['id']);
                
                $updatedReservation = Reservation::find($params['id']);
                return Response::success($updatedReservation, 'Check-in başarılı');
            }
            
            return Response::error('Check-in yapılamadı', 400);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * POST /api/reservations/{id}/checkout
     * Check out reservation
     */
    public function checkOut(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('reservations.checkout');
            
            $reservation = Reservation::find($params['id']);
            if (!$reservation) {
                return Response::notFound('Rezervasyon bulunamadı');
            }
            
            if ($reservation['status'] !== 'checked_in') {
                return Response::error('Sadece check-in yapılmış rezervasyonlar check-out yapılabilir', 400);
            }
            
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $totalPrice = $input['total_price'] ?? $reservation['total_price'];
            
            if (Reservation::checkOut($params['id'], $totalPrice) && 
                Room::changeStatus($reservation['room_id'], 'dirty')) {
                
                // Update customer statistics
                Customer::updateStats($reservation['customer_id'], $totalPrice);
                
                Logger::audit('reservation_checked_out', 'reservation', $params['id']);
                
                $updatedReservation = Reservation::find($params['id']);
                return Response::success($updatedReservation, 'Check-out başarılı');
            }
            
            return Response::error('Check-out yapılamadı', 400);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * DELETE /api/reservations/{id}
     * Cancel reservation
     */
    public function cancel(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('reservations.delete');
            
            $reservation = Reservation::find($params['id']);
            if (!$reservation) {
                return Response::notFound('Rezervasyon bulunamadı');
            }
            
            if (Reservation::cancel($params['id'])) {
                // Release room if occupied
                if ($reservation['status'] === 'checked_in') {
                    Room::changeStatus($reservation['room_id'], 'available');
                }
                
                Logger::audit('reservation_cancelled', 'reservation', $params['id']);
                return Response::success(null, 'Rezervasyon başarılı iptal edildi');
            }
            
            return Response::error('Rezervasyon iptal edilemedi', 400);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/reservations/upcoming
     * Get upcoming reservations
     */
    public function upcoming(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('reservations.view');
            
            $days = isset($_GET['days']) ? (int)$_GET['days'] : 7;
            $reservations = Reservation::upcoming($days);
            
            return Response::success($reservations, 'Yaklaşan rezervasyonlar');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/reservations/calendar
     * Get reservations for calendar view
     */
    public function calendar(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('reservations.view');
            
            $from = $_GET['from'] ?? date('Y-m-01');
            $to = $_GET['to'] ?? date('Y-m-t');
            
            $reservations = Reservation::byDateRange($from, $to);
            
            return Response::success($reservations, 'Takvim rezervasyonları');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}
