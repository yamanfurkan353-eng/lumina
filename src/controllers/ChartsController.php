<?php
/**
 * Charts Controller
 * Returns chart data for dashboard and analytics
 */

namespace HotelMaster\Controllers;

use HotelMaster\Core\Auth;
use HotelMaster\Core\Response;
use HotelMaster\Core\Database;

class ChartsController {
    
    /**
     * GET /api/charts/revenue
     * Get revenue chart data (last 12 months)
     */
    public function revenueChart(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            $db = Database::getInstance();
            $months = [];
            $values = [];
            
            // Generate last 12 months data
            for ($i = 11; $i >= 0; $i--) {
                $date = new \DateTime("now -{$i} months");
                $month = $date->format('Y-m');
                
                $monthAbbr = [
                    '01' => 'Oca', '02' => 'Şub', '03' => 'Mar', '04' => 'Nis',
                    '05' => 'May', '06' => 'Haz', '07' => 'Tem', '08' => 'Ağu',
                    '09' => 'Eyl', '10' => 'Eki', '11' => 'Kas', '12' => 'Ara'
                ][$date->format('m')];
                
                $months[] = $monthAbbr;
                
                // Calculate revenue for this month
                $sql = "
                    SELECT COALESCE(SUM((DATEDIFF(check_out, check_in) * (SELECT price_per_night FROM rooms WHERE id = reservations.room_id))), 0) as total
                    FROM reservations
                    WHERE DATE_FORMAT(check_in, '%Y-%m') = ? 
                    AND status IN ('checked_out', 'checked_in', 'confirmed')
                ";
                
                $result = $db->query($sql, [$month])->fetch(\PDO::FETCH_ASSOC);
                $values[] = (int)($result['total'] ?? 0);
            }
            
            return Response::success([
                'labels' => $months,
                'values' => $values
            ], 'Revenue chart data');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/charts/occupancy
     * Get occupancy chart data
     */
    public function occupancyChart(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            $db = Database::getInstance();
            $today = date('Y-m-d');
            
            // Total rooms
            $totalResult = $db->query("SELECT COUNT(*) as count FROM rooms")->fetch(\PDO::FETCH_ASSOC);
            $totalRooms = $totalResult['count'] ?? 0;
            
            // Occupied rooms today
            $occupiedResult = $db->query("
                SELECT COUNT(DISTINCT room_id) as count 
                FROM reservations 
                WHERE status IN ('confirmed', 'checked_in')
                AND DATE(check_in) <= ? 
                AND DATE(check_out) > ?
            ", [$today, $today])->fetch(\PDO::FETCH_ASSOC);
            
            $occupiedRooms = $occupiedResult['count'] ?? 0;
            $available = $totalRooms - $occupiedRooms;
            
            return Response::success([
                'occupied' => $occupiedRooms,
                'available' => $available,
                'total' => $totalRooms,
                'occupancy_rate' => $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0
            ], 'Occupancy chart data');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/charts/room-types
     * Get room type distribution chart
     */
    public function roomTypesChart(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            $db = Database::getInstance();
            
            $result = $db->query("
                SELECT room_type, COUNT(*) as count
                FROM rooms
                GROUP BY room_type
            ")->fetchAll(\PDO::FETCH_ASSOC);
            
            $types = [];
            $counts = [];
            
            foreach ($result as $row) {
                $types[] = $row['room_type'];
                $counts[] = (int)$row['count'];
            }
            
            return Response::success([
                'labels' => $types,
                'values' => $counts
            ], 'Room types chart data');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/charts/booking-status
     * Get booking status distribution
     */
    public function bookingStatusChart(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            $db = Database::getInstance();
            
            $statuses = [
                'confirmed' => 'Onaylandı',
                'checked_in' => 'Konaklamada',
                'checked_out' => 'Ayrıldı',
                'cancelled' => 'İptal'
            ];
            
            $labels = [];
            $values = [];
            
            foreach ($statuses as $status => $label) {
                $result = $db->query("
                    SELECT COUNT(*) as count 
                    FROM reservations 
                    WHERE status = ?
                    AND DATE(check_in) >= ?
                    AND DATE(check_in) <= ?
                ", [$status, date('Y-m-01'), date('Y-m-t')])->fetch(\PDO::FETCH_ASSOC);
                
                $count = $result['count'] ?? 0;
                if ($count > 0) {
                    $labels[] = $label;
                    $values[] = (int)$count;
                }
            }
            
            return Response::success([
                'labels' => $labels,
                'values' => $values
            ], 'Booking status chart data');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}
?>
