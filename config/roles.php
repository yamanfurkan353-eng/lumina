<?php
/**
 * Role-Based Access Control (RBAC) Configuration
 * 
 * Defines user roles and their permissions.
 */

return [
    'admin' => [
        'name' => 'Yönetici',
        'description' => 'Tam sistem erişimi',
        'permissions' => [
            // User Management
            'users.view' => 'Kullanıcıları Görüntüle',
            'users.create' => 'Kullanıcı Oluştur',
            'users.edit' => 'Kullanıcı Düzenle',
            'users.delete' => 'Kullanıcı Sil',
            
            // Room Management
            'rooms.view' => 'Odaları Görüntüle',
            'rooms.create' => 'Oda Oluştur',
            'rooms.edit' => 'Oda Düzenle',
            'rooms.delete' => 'Oda Sil',
            'rooms.change_status' => 'Oda Durumunu Değiştir',
            
            // Reservation Management
            'reservations.view' => 'Rezervasyonları Görüntüle',
            'reservations.create' => 'Rezervasyon Oluştur',
            'reservations.edit' => 'Rezervasyon Düzenle',
            'reservations.delete' => 'Rezervasyon Sil',
            'reservations.checkin' => 'Check-in Yap',
            'reservations.checkout' => 'Check-out Yap',
            
            // Customer Management
            'customers.view' => 'Müşterileri Görüntüle',
            'customers.create' => 'Müşteri Oluştur',
            'customers.edit' => 'Müşteri Düzenle',
            'customers.delete' => 'Müşteri Sil',
            
            // Reports & Exports
            'reports.view' => 'Raporları Görüntüle',
            'exports.csv' => 'CSV İhraç',
            'exports.pdf' => 'PDF İhraç',
            
            // Settings
            'settings.view' => 'Ayarları Görüntüle',
            'settings.edit' => 'Ayarları Düzenle',
            'settings.backup' => 'Yedek Al',
            'settings.restore' => 'Yedekten Geri Yükle',
            'audit_log.view' => 'Etkinlik Günlüğünü Görüntüle',
        ]
    ],
    
    'receptionist' => [
        'name' => 'Resepsiyon',
        'description' => 'Rezervasyon ve oda yönetimi',
        'permissions' => [
            // Room Management (read-only, view status)
            'rooms.view' => 'Odaları Görüntüle',
            'rooms.change_status' => 'Oda Durumunu Değiştir',
            
            // Reservation Management (full access)
            'reservations.view' => 'Rezervasyonları Görüntüle',
            'reservations.create' => 'Rezervasyon Oluştur',
            'reservations.edit' => 'Rezervasyon Düzenle',
            'reservations.delete' => 'Rezervasyon Sil',
            'reservations.checkin' => 'Check-in Yap',
            'reservations.checkout' => 'Check-out Yap',
            
            // Customer Management (full access)
            'customers.view' => 'Müşterileri Görüntüle',
            'customers.create' => 'Müşteri Oluştur',
            'customers.edit' => 'Müşteri Düzenle',
            
            // Reports (read-only)
            'reports.view' => 'Raporları Görüntüle',
            'exports.csv' => 'CSV İhraç',
            'exports.pdf' => 'PDF İhraç',
        ]
    ],
    
    'housekeeping' => [
        'name' => 'Oda Temizliği',
        'description' => 'Oda durumu yönetimi',
        'permissions' => [
            // Room Management (view and status update only)
            'rooms.view' => 'Odaları Görüntüle',
            'rooms.change_status' => 'Oda Durumunu Değiştir',
            
            // Reservation Management (read-only)
            'reservations.view' => 'Rezervasyonları Görüntüle',
        ]
    ]
];
