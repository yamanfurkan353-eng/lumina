# Veritabanı Şeması - Hotel Master Lite

## Tablolar

### 1. `users`
Sistem kullanıcılarını yönetir (Admin, Resepsiyon, Oda Temizliği)

| Alan | Tip | Açıklama |
|------|-----|----------|
| `id` | INTEGER PK | Benzersiz kullanıcı ID |
| `name` | VARCHAR(255) | Kullanıcı adı soyadı |
| `email` | VARCHAR(255) UK | E-posta adresi (benzersiz) |
| `password_hash` | VARCHAR(255) | Bcrypt-şifreli şifre |
| `role` | VARCHAR(50) | Rol: admin, receptionist, housekeeping |
| `phone` | VARCHAR(20) | Telefon numarası |
| `is_active` | BOOLEAN | Hesap aktif mi? |
| `last_login` | DATETIME | Son giriş zamanı |
| `created_at` | DATETIME | Oluşturma zamanı |
| `updated_at` | DATETIME | Güncelleme zamanı |

---

### 2. `rooms`
Otel odalarını tanımlar

| Alan | Tip | Açıklama |
|------|-----|----------|
| `id` | INTEGER PK | Benzersiz oda ID |
| `room_number` | VARCHAR(10) UK | Oda numarası (örn: 101, 202) |
| `room_type` | VARCHAR(50) | Tip: single, double, suite, deluxe |
| `capacity` | INTEGER | Yatak kapasitesi |
| `price_per_night` | DECIMAL(10,2) | Gecelik fiyat (₺) |
| `status` | VARCHAR(50) | Durum: available, occupied, dirty, maintenance |
| `floor` | INTEGER | Oda bulunduğu kat |
| `amenities` | TEXT (JSON) | ["WiFi", "TV", "Minibar"] |
| `notes` | TEXT | Ek notlar |
| `created_at` | DATETIME | Oluşturma zamanı |
| `updated_at` | DATETIME | Güncelleme zamanı |

**Durumlar**:
- `available` - Müsait
- `occupied` - Dolu
- `dirty` - Temizlenecek
- `maintenance` - Bakımda

---

### 3. `customers`
Misafir/müşteri bilgileri

| Alan | Tip | Açıklama |
|------|-----|----------|
| `id` | INTEGER PK | Benzersiz müşteri ID |
| `first_name` | VARCHAR(100) | Ad |
| `last_name` | VARCHAR(100) | Soyad |
| `email` | VARCHAR(255) | E-posta |
| `phone` | VARCHAR(20) | Telefon (zorunlu) |
| `national_id` | VARCHAR(20) | Kimlik numarası |
| `address` | TEXT | Adres |
| `city` | VARCHAR(100) | Şehir |
| `country` | VARCHAR(100) | Ülke (varsayılan: Türkiye) |
| `birth_date` | DATE | Doğum tarihi |
| `notes` | TEXT | Ek notlar |
| `total_stays` | INTEGER | Toplam konaklama sayısı |
| `total_spent` | DECIMAL(10,2) | Toplam harcama |
| `created_at` | DATETIME | Oluşturma zamanı |
| `updated_at` | DATETIME | Güncelleme zamanı |

---

### 4. `reservations`
Rezervasyon bilgileri

| Alan | Tip | Açıklama |
|------|-----|----------|
| `id` | INTEGER PK | Benzersiz rezervasyon ID |
| `customer_id` | INTEGER FK | Müşteri referansı |
| `room_id` | INTEGER FK | Oda referansı |
| `check_in` | DATE | Giriş tarihi |
| `check_out` | DATE | Çıkış tarihi |
| `number_of_guests` | INTEGER | Konuk sayısı |
| `total_price` | DECIMAL(10,2) | Toplam fiyat |
| `status` | VARCHAR(50) | Durum (aşağıya bakınız) |
| `payment_status` | VARCHAR(50) | Ödeme durumu |
| `notes` | TEXT | Ek notlar |
| `created_by` | INTEGER FK | Oluşturan kullanıcı |
| `created_at` | DATETIME | Oluşturma zamanı |
| `updated_at` | DATETIME | Güncelleme zamanı |

**Durumlar**:
- `confirmed` - Onaylanmış
- `checked_in` - Misafir konaklamada
- `checked_out` - Ayrılmış
- `cancelled` - İptal edilmiş

**Ödeme Durumları**:
- `pending` - Beklemede
- `partial` - Kısmi ödeme
- `paid` - Ödendi

---

### 5. `audit_log`
Denetim ve etkinlik günlüğü

| Alan | Tip | Açıklama |
|------|-----|----------|
| `id` | INTEGER PK | Benzersiz günlük ID |
| `user_id` | INTEGER FK | İşlemi yapan kullanıcı |
| `action` | VARCHAR(255) | İşlem: login, password_changed, vb |
| `entity_type` | VARCHAR(100) | Varlık tipi: user, room, reservation, customer |
| `entity_id` | INTEGER | Varlık ID'si |
| `old_values` | TEXT (JSON) | Eski değerler |
| `new_values` | TEXT (JSON) | Yeni değerler |
| `ip_address` | VARCHAR(45) | İstemci IP adresi |
| `created_at` | DATETIME | Etkinlik zamanı |

---

### 6. `settings`
Sistem ayarları

| Alan | Tip | Açıklama |
|------|-----|----------|
| `id` | INTEGER PK | Benzersiz ayar ID |
| `key` | VARCHAR(255) UK | Ayar anahtarı |
| `value` | TEXT | Ayar değeri |
| `type` | VARCHAR(50) | Veri tipi: string, int, bool, json |
| `updated_at` | DATETIME | Güncelleme zamanı |

**Varsayılan Ayarlar**:
- `hotel_name` - Otel adı
- `currency` - Para birimi (TRY)
- `currency_symbol` - Para sembolü (₺)
- `check_in_time` - Check-in saati (14:00)
- `check_out_time` - Check-out saati (11:00)
- `timezone` - Saat dilimi (Europe/Istanbul)
- `language` - Dil (tr)

---

## Yazılı Parametreler & Dizinler

### Primary Keys
- Her tablo bir `id` sütununa sahip

### Unique Keys
- `users.email`
- `rooms.room_number`
- `settings.key`

### Foreign Keys
- `reservations.customer_id` → `customers.id`
- `reservations.room_id` → `rooms.id`
- `reservations.created_by` → `users.id`
- `audit_log.user_id` → `users.id`

### Dizinler
```sql
-- Performans için oluşturulan dizinler
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_rooms_status ON rooms(status);
CREATE INDEX idx_reservations_customer_id ON reservations(customer_id);
CREATE INDEX idx_reservations_room_id ON reservations(room_id);
CREATE INDEX idx_reservations_check_in ON reservations(check_in);
CREATE INDEX idx_reservations_check_out ON reservations(check_out);
```

---

## Veritabanı Normalizasyonu

- **1NF (First Normal Form)**: Her sütun atomik değerleri tutar
- **2NF (Second Normal Form)**: Tüm sütunlar anahtar özniteliklere bağlıdır
- **3NF (Third Normal Form)**: Geçişsiz bağımlılıklar yoktur

---

## SQL Örnekleri

### Bugünün Doluluk Oranı
```sql
SELECT 
    COUNT(*) as total_rooms,
    SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied,
    ROUND(SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as occupancy_rate
FROM rooms;
```

### Bu Ayın Geliri
```sql
SELECT 
    SUM(total_price) as monthly_revenue,
    COUNT(*) as reservation_count,
    AVG(total_price) as avg_price
FROM reservations
WHERE strftime('%Y-%m', check_out) = strftime('%Y-%m', 'now')
AND status IN ('checked_out', 'confirmed');
```

### Müşteri Konaklamaları
```sql
SELECT 
    c.*, 
    COUNT(r.id) as stay_count,
    SUM(r.total_price) as total_spent
FROM customers c
LEFT JOIN reservations r ON c.id = r.customer_id
GROUP BY c.id
ORDER BY total_spent DESC;
```

### Yaklaşan Rezervasyonlar
```sql
SELECT 
    r.*, 
    c.first_name, 
    c.last_name, 
    c.phone,
    room.room_number
FROM reservations r
JOIN customers c ON r.customer_id = c.id
JOIN rooms room ON r.room_id = room.id
WHERE r.check_in BETWEEN DATE('now') AND DATE('now', '+7 days')
AND r.status IN ('confirmed', 'checked_in')
ORDER BY r.check_in;
```

---

**Son Güncelleme**: Şubat 2026
