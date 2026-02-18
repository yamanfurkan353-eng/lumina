<?php
/**
 * Export Controller
 * 
 * Handles CSV and PDF exports
 */

namespace HotelMaster\Controllers;

use HotelMaster\Core\Auth;
use HotelMaster\Core\Response;
use HotelMaster\Models\Reservation;

class ExportController {
    
    /**
     * GET /api/export/reservations/csv
     * Export reservations to CSV
     */
    public function reservationsCSV(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('exports.csv');
            
            $from = $_GET['from'] ?? date('Y-m-01');
            $to = $_GET['to'] ?? date('Y-m-d');
            
            $reservations = Reservation::byDateRange($from, $to);
            
            $filename = 'reservations_' . date('Y-m-d_H-i-s') . '.csv';
            $filepath = EXPORTS_PATH . '/' . $filename;
            
            // Create CSV content
            $csv = "Rezervasyon ID,Müşteri Adı,Telefon,Oda No,Check-in,Check-out,Konuk Sayısı,Toplam Fiyat,Ödeme Durumu,Durum,Not\n";
            
            foreach ($reservations as $res) {
                $csv .= sprintf(
                    '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                    $res['id'],
                    $res['first_name'] . ' ' . $res['last_name'],
                    $res['phone'],
                    $res['room_number'],
                    $res['check_in'],
                    $res['check_out'],
                    $res['number_of_guests'],
                    $res['total_price'],
                    $res['payment_status'],
                    $res['status'],
                    str_replace('"', '""', $res['notes'] ?? '')
                );
            }
            
            file_put_contents($filepath, $csv);
            
            return Response::success([
                'filename' => $filename,
                'path' => "/exports/{$filename}",
                'rows' => count($reservations)
            ], 'CSV başarılı oluşturuldu');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/export/customers/csv
     * Export customers to CSV
     */
    public function customersCSV(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('exports.csv');
            
            $customers = \HotelMaster\Models\Customer::all();
            
            $filename = 'customers_' . date('Y-m-d_H-i-s') . '.csv';
            $filepath = EXPORTS_PATH . '/' . $filename;
            
            // Create CSV content
            $csv = "Müşteri ID,Ad,Soyad,E-posta,Telefon,Şehir,Ülke,Toplam Konaklama,Toplam Harcama\n";
            
            foreach ($customers as $customer) {
                $csv .= sprintf(
                    '"%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                    $customer['id'],
                    $customer['first_name'],
                    $customer['last_name'],
                    $customer['email'] ?? '',
                    $customer['phone'],
                    $customer['city'] ?? '',
                    $customer['country'],
                    $customer['total_stays'],
                    $customer['total_spent']
                );
            }
            
            file_put_contents($filepath, $csv);
            
            return Response::success([
                'filename' => $filename,
                'path' => "/exports/{$filename}",
                'rows' => count($customers)
            ], 'CSV başarılı oluşturuldu');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/export/rooms/csv
     * Export rooms to CSV
     */
    public function roomsCSV(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('exports.csv');
            
            $rooms = \HotelMaster\Models\Room::all();
            
            $filename = 'rooms_' . date('Y-m-d_H-i-s') . '.csv';
            $filepath = EXPORTS_PATH . '/' . $filename;
            
            // Create CSV content
            $csv = "Oda No,Oda Tipi,Kapasite,Gecelik Fiyat,Durum,Kat,Not\n";
            
            foreach ($rooms as $room) {
                $csv .= sprintf(
                    '"%s","%s","%s","%s","%s","%s","%s"' . "\n",
                    $room['room_number'],
                    $room['room_type'],
                    $room['capacity'],
                    $room['price_per_night'],
                    $room['status'],
                    $room['floor'],
                    str_replace('"', '""', $room['notes'] ?? '')
                );
            }
            
            file_put_contents($filepath, $csv);
            
            return Response::success([
                'filename' => $filename,
                'path' => "/exports/{$filename}",
                'rows' => count($rooms)
            ], 'CSV başarılı oluşturuldu');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/export/reservation/{id}/pdf
     * Generate PDF invoice for a reservation
     */
    public function generateReservationPDF(array $params): void {
        try {
            if (!Auth::isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                return;
            }
            
            $id = $params['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid reservation ID']);
                return;
            }
            
            $reservation = Reservation::find($id);
            if (!$reservation) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Reservation not found']);
                return;
            }
            
            // Generate HTML invoice
            $html = $this->generateInvoiceHTML($reservation);
            
            // For now, return HTML that can be printed (PDF generation requires external library)
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="Invoice-RES-' . $id . '.html"');
            echo $html;
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Generate HTML invoice content
     */
    private function generateInvoiceHTML($reservation): string {
        $nights = (new \DateTime($reservation['check_out']))->diff(new \DateTime($reservation['check_in']))->days ?: 1;
        $totalAmount = $nights * ($reservation['room_price'] ?? 0);
        $pricePerNight = number_format($reservation['room_price'] ?? 0, 2, ',', '.');
        $totalDisplay = number_format($totalAmount, 2, ',', '.');
        
        return <<<HTML
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Fatura - RES-{$reservation['id']}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; color: #1f2937; background: white; }
                .container { max-width: 800px; margin: 0 auto; }
                .header { text-align: center; border-bottom: 3px solid #c4886c; padding-bottom: 20px; margin-bottom: 30px; }
                .header h1 { color: #c4886c; margin: 0; font-size: 28px; }
                .header p { color: #6b7280; margin: 5px 0; }
                .section { margin-bottom: 25px; }
                .section-title { background: #f3f4f6; padding: 12px; font-weight: bold; font-size: 14px; margin-bottom: 12px; color: #1f2937; border-left: 4px solid #c4886c; }
                .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
                .row label { font-weight: 600; color: #374151; }
                .row span { color: #6b7280; }
                .total { background: #fffbeb; border-top: 2px solid #c4886c; padding: 20px; text-align: right; font-size: 20px; font-weight: bold; margin-top: 30px; }
                .amount { color: #c4886c; }
                .footer { text-align: center; margin-top: 40px; color: #9ca3af; font-size: 12px; border-top: 1px solid #e5e7eb; padding-top: 20px; }
                .print-button { text-align: center; margin-bottom: 20px; }
                .print-button button { padding: 10px 20px; background: #c4886c; color: white; border: none; cursor: pointer; font-size: 14px; }
                @media print {
                    .print-button { display: none; }
                    body { margin: 0; padding: 0; }
                }
            </style>
        </head>
        <body>
            <div class="print-button">
                <button onclick="window.print()">🖨️ Yazdır</button>
            </div>
            
            <div class="container">
                <div class="header">
                    <h1>HOTEL MASTER LITE</h1>
                    <p style="font-size: 16px;">Rezervasyon Faturası</p>
                </div>
                
                <div class="section">
                    <div class="section-title">📋 Fatura Bilgileri</div>
                    <div class="row">
                        <label>Fatura No:</label>
                        <span>RES-{$reservation['id']}</span>
                    </div>
                    <div class="row">
                        <label>İşlem Tarihi:</label>
                        <span>{{date_now}}</span>
                    </div>
                </div>
                
                <div class="section">
                    <div class="section-title">👤 Müşteri Bilgileri</div>
                    <div class="row">
                        <label>Ad Soyad:</label>
                        <span>{$reservation['first_name']} {$reservation['last_name']}</span>
                    </div>
                    <div class="row">
                        <label>Telefon:</label>
                        <span>{$reservation['phone']}</span>
                    </div>
                    <div class="row">
                        <label>Email:</label>
                        <span>{$reservation['email']}</span>
                    </div>
                </div>
                
                <div class="section">
                    <div class="section-title">🏨 Rezervasyon Detayları</div>
                    <div class="row">
                        <label>Oda No:</label>
                        <span>{$reservation['room_number']}</span>
                    </div>
                    <div class="row">
                        <label>Oda Tipi:</label>
                        <span>{$reservation['room_type']}</span>
                    </div>
                    <div class="row">
                        <label>Check-in:</label>
                        <span>{{check_in_date}}</span>
                    </div>
                    <div class="row">
                        <label>Check-out:</label>
                        <span>{{check_out_date}}</span>
                    </div>
                    <div class="row">
                        <label>Gece Sayısı:</label>
                        <span>{$nights} gece</span>
                    </div>
                </div>
                
                <div class="section">
                    <div class="section-title">💰 Ödeme Detayları</div>
                    <div class="row">
                        <label>Gece Ücreti:</label>
                        <span>₺{$pricePerNight}</span>
                    </div>
                    <div class="row">
                        <label>Gece Sayısı:</label>
                        <span>{$nights}</span>
                    </div>
                </div>
                
                <div class="total">
                    Toplam Tutar: <span class="amount">₺{$totalDisplay}</span>
                </div>
                
                <div class="footer">
                    <p>Teşekkür ederiz! Hotel Master Lite tarafından oluşturulmuştur.</p>
                    <p>Bu fatura yasal bir belgedir.</p>
                </div>
            </div>
            
            <script>
                // Replace template variables with actual dates
                document.body.innerHTML = document.body.innerHTML
                    .replace('{{date_now}}', new Date().toLocaleDateString('tr-TR'))
                    .replace('{{check_in_date}}', new Date('{$reservation['check_in']}').toLocaleDateString('tr-TR'))
                    .replace('{{check_out_date}}', new Date('{$reservation['check_out']}').toLocaleDateString('tr-TR'));
            </script>
        </body>
        </html>
        HTML;
    }
}

