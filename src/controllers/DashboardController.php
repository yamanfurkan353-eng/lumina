<?php
/**
 * Dashboard Controller
 * 
 * Handles dashboard data and statistics
 */

namespace HotelMaster\Controllers;

use HotelMaster\Core\Auth;
use HotelMaster\Core\Response;
use HotelMaster\Models\Room;
use HotelMaster\Models\Reservation;
use HotelMaster\Models\Customer;

class DashboardController {
    
    /**
     * GET /api/dashboard
     * Get dashboard overview data
     */
    public function index(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            $today = date('Y-m-d');
            $thisMonth = date('Y-m-01');
            $nextMonth = date('Y-m-01', strtotime('+1 month'));
            
            // Room statistics
            $totalRooms = Room::count();
            $occupiedRooms = Room::countOccupied();
            $availableRooms = Room::countAvailable();
            $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 2) : 0;
            
            // Reservation statistics
            $todayCheckIns = Reservation::todayCheckIns();
            $todayCheckOuts = Reservation::todayCheckOuts();
            $upcomingReservations = Reservation::upcoming(7);
            
            // Revenue statistics
            $monthlyStats = Reservation::revenueStats($thisMonth, $nextMonth);
            
            // Customer count
            $totalCustomers = Customer::count();
            
            // Chart data - Last 12 months revenue
            $chartData = $this->getLast12MonthsRevenue();
            
            return Response::success([
                'rooms' => [
                    'total' => $totalRooms,
                    'occupied' => $occupiedRooms,
                    'available' => $availableRooms,
                    'occupancy_rate' => $occupancyRate
                ],
                'reservations' => [
                    'today_checkins' => count($todayCheckIns),
                    'today_checkouts' => count($todayCheckOuts),
                    'upcoming_7_days' => count($upcomingReservations)
                ],
                'revenue' => [
                    'this_month' => $monthlyStats['total_revenue'] ?? 0,
                    'total_reservations' => $monthlyStats['total_reservations'] ?? 0,
                    'average_price' => $monthlyStats['avg_price'] ?? 0,
                    'monthly_labels' => $chartData['labels'],
                    'monthly_values' => $chartData['values']
                ],
                'customers' => [
                    'total' => $totalCustomers
                ],
                'upcoming_checkins' => array_slice($todayCheckIns, 0, 5),
                'upcoming_checkouts' => array_slice($todayCheckOuts, 0, 5)
            ], 'Dashboard verileri');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * Get revenue data for last 12 months (for charts)
     */
    private function getLast12MonthsRevenue(): array {
        $months = [];
        $values = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = new \DateTime("now -{$i} months");
            $month = $date->format('Y-m');
            
            $monthAbbr = [
                '01' => 'Oca', '02' => 'Şub', '03' => 'Mar', '04' => 'Nis',
                '05' => 'May', '06' => 'Haz', '07' => 'Tem', '08' => 'Ağu',
                '09' => 'Eyl', '10' => 'Eki', '11' => 'Kas', '12' => 'Ara'
            ][$date->format('m')];
            
            $months[] = $monthAbbr;
            
            $thisMonth = date('Y-m-01', strtotime($month . '-01'));
            $nextMonth = date('Y-m-01', strtotime($month . '-01 +1 month'));
            $stats = Reservation::revenueStats($thisMonth, $nextMonth);
            $values[] = (int)($stats['total_revenue'] ?? 0);
        }
        
        return [
            'labels' => $months,
            'values' => $values
        ];
    }
}
