<?php
/**
 * Room Controller
 * 
 * Handles room management endpoints
 */

namespace HotelMaster\Controllers;

use HotelMaster\Core\Auth;
use HotelMaster\Core\Response;
use HotelMaster\Core\Logger;
use HotelMaster\Models\Room;
use HotelMaster\Utils\Validator;

class RoomController {
    
    /**
     * GET /api/rooms
     * Get all rooms
     */
    public function list(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('rooms.view');
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = ITEMS_PER_PAGE;
            $offset = ($page - 1) * $perPage;
            
            $rooms = Room::all($perPage, $offset);
            $total = Room::count();
            
            return Response::paginated($rooms, $total, $page, $perPage, 'Odalar başarıyla alındı');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/rooms/{id}
     * Get single room
     */
    public function show(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('rooms.view');
            
            $room = Room::find($params['id']);
            
            if (!$room) {
                return Response::notFound('Oda bulunamadı');
            }
            
            // Parse amenities JSON
            if ($room['amenities']) {
                $room['amenities'] = json_decode($room['amenities'], true);
            }
            
            return Response::success($room);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * POST /api/rooms
     * Create new room
     */
    public function store(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('rooms.create');
            
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            $validator = new Validator($input);
            $validator->required('room_number')
                     ->required('room_type')
                     ->required('price_per_night')
                     ->numeric('price_per_night');
            
            if ($validator->fails()) {
                return Response::validationError($validator->errors());
            }
            
            $roomId = Room::create($input);
            
            if (!$roomId) {
                return Response::error('Oda oluşturulamadı', 400);
            }
            
            Logger::audit('room_created', 'room', $roomId, null, $input);
            
            $room = Room::find($roomId);
            return Response::created($room, 'Oda başarılı oluşturuldu');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * PUT /api/rooms/{id}
     * Update room
     */
    public function update(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('rooms.edit');
            
            $room = Room::find($params['id']);
            if (!$room) {
                return Response::notFound('Oda bulunamadı');
            }
            
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            if (Room::update($params['id'], $input)) {
                Logger::audit('room_updated', 'room', $params['id'], $room, $input);
                
                $updatedRoom = Room::find($params['id']);
                if ($updatedRoom['amenities']) {
                    $updatedRoom['amenities'] = json_decode($updatedRoom['amenities'], true);
                }
                
                return Response::success($updatedRoom, 'Oda başarılı güncellendi');
            }
            
            return Response::error('Oda güncellenemedi', 400);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * PUT /api/rooms/{id}/status
     * Change room status
     */
    public function changeStatus(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('rooms.change_status');
            
            $room = Room::find($params['id']);
            if (!$room) {
                return Response::notFound('Oda bulunamadı');
            }
            
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            $validator = new Validator($input);
            $validator->required('status')
                     ->in('status', ['available', 'occupied', 'dirty', 'maintenance']);
            
            if ($validator->fails()) {
                return Response::validationError($validator->errors());
            }
            
            $oldStatus = $room['status'];
            
            if (Room::changeStatus($params['id'], $input['status'])) {
                Logger::audit('room_status_changed', 'room', $params['id'], 
                             ['status' => $oldStatus], 
                             ['status' => $input['status']]);
                
                $updatedRoom = Room::find($params['id']);
                return Response::success($updatedRoom, 'Oda durumu başarılı değiştirildi');
            }
            
            return Response::error('Oda durumu değiştirilemedi', 400);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * DELETE /api/rooms/{id}
     * Delete room
     */
    public function delete(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('rooms.delete');
            
            $room = Room::find($params['id']);
            if (!$room) {
                return Response::notFound('Oda bulunamadı');
            }
            
            if (Room::delete($params['id'])) {
                Logger::audit('room_deleted', 'room', $params['id'], $room);
                return Response::success(null, 'Oda başarılı silindi');
            }
            
            return Response::error('Oda silinemedi', 400);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/rooms/available
     * Get available rooms for date range
     */
    public function available(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('rooms.view');
            
            $checkIn = $_GET['check_in'] ?? null;
            $checkOut = $_GET['check_out'] ?? null;
            
            $validator = new Validator(['check_in' => $checkIn, 'check_out' => $checkOut]);
            $validator->required('check_in')->required('check_out');
            
            if ($validator->fails()) {
                return Response::validationError($validator->errors());
            }
            
            $rooms = Room::available($checkIn, $checkOut);
            
            return Response::success($rooms, 'Müsait odalar');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}
