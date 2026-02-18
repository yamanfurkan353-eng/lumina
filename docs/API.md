# API Belgeleri - Hotel Master Lite

Bu dokümantasyon tüm API endpoint'lerini açıklar.

## İçindekiler
1. [Kimlik Doğrulama](#kimlik-doğrulama)
2. [Oda Yönetimi](#oda-yönetimi)
3. [Müşteri Yönetimi](#müşteri-yönetimi)
4. [Rezervasyon Yönetimi](#rezervasyon-yönetimi)
5. [Ayarlar](#ayarlar)
6. [Dışa Aktarım](#dışa-aktarım)

## Temel Bilgiler

**Base URL**: `http://localhost:8000/api`

**Content-Type**: `application/json`

**Standart Yanıt Yapısı**:
```json
{
  "status": "success|error",
  "message": "İşlem mesajı",
  "data": {},
  "timestamp": "2026-02-18 12:00:00"
}
```

---

## Kimlik Doğrulama

### Giriş (Login)

**Endpoint**: `POST /auth/login`

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@hotel.local",
    "password": "admin123"
  }'
```

**Yanıt**:
```json
{
  "status": "success",
  "message": "Başarılı giriş",
  "data": {
    "user": {
      "id": 1,
      "name": "Yönetici",
      "email": "admin@hotel.local",
      "role": "admin"
    },
    "csrf_token": "..."
  }
}
```

### Kullanıcı Bilgisi

**Endpoint**: `GET /auth/user`

```bash
curl -X GET http://localhost:8000/api/auth/user \
  -H "Authorization: Bearer TOKEN"
```

### Çıkış (Logout)

**Endpoint**: `POST /auth/logout`

```bash
curl -X POST http://localhost:8000/api/auth/logout
```

---

## Oda Yönetimi

### Odaları Listele

**Endpoint**: `GET /rooms?page=1`

```bash
curl http://localhost:8000/api/rooms
```

**Parametreler**:
- `page` - Sayfa numarası (varsayılan: 1)

### Oda Detayları

**Endpoint**: `GET /rooms/{id}`

```bash
curl http://localhost:8000/api/rooms/1
```

### Oda Oluştur

**Endpoint**: `POST /rooms`

**İzin**: `rooms.create` (Admin)

```json
{
  "room_number": "101",
  "room_type": "double",
  "capacity": 2,
  "price_per_night": 500.00,
  "floor": 1,
  "amenities": ["WiFi", "TV", "Minibar"]
}
```

### Oda Güncelle

**Endpoint**: `PUT /rooms/{id}`

**İzin**: `rooms.edit` (Admin, Resepsiyon)

### Oda Durumunu Değiştir

**Endpoint**: `PUT /rooms/{id}/status`

```json
{
  "status": "available"
}
```

**Durum Değerleri**: `available`, `occupied`, `dirty`, `maintenance`

### Müsait Odaları Listele

**Endpoint**: `GET /rooms/available?check_in=2026-02-20&check_out=2026-02-22`

```bash
curl "http://localhost:8000/api/rooms/available?check_in=2026-02-20&check_out=2026-02-22"
```

---

## Müşteri Yönetimi

### Müşterileri Listele

**Endpoint**: `GET /customers?page=1`

### Müşteri Oluştur

**Endpoint**: `POST /customers`

```json
{
  "first_name": "Ahmet",
  "last_name": "Yılmaz",
  "phone": "05551234567",
  "email": "ahmet@example.com",
  "national_id": "12345678901",
  "address": "Ankara",
  "city": "Ankara",
  "country": "Türkiye"
}
```

### Müşteri Ara

**Endpoint**: `GET /customers/search?q=Ahmet`

---

## Rezervasyon Yönetimi

### Rezervasyonları Listele

**Endpoint**: `GET /reservations?page=1`

### Rezervasyon Oluştur

**Endpoint**: `POST /reservations`

```json
{
  "customer_id": 1,
  "room_id": 1,
  "check_in": "2026-02-20",
  "check_out": "2026-02-22",
  "number_of_guests": 2,
  "notes": "Erkek misafir"
}
```

### Check-in

**Endpoint**: `POST /reservations/{id}/checkin`

```bash
curl -X POST http://localhost:8000/api/reservations/1/checkin
```

### Check-out

**Endpoint**: `POST /reservations/{id}/checkout`

```json
{
  "total_price": 1000.00
}
```

### Rezervasyon İptal

**Endpoint**: `DELETE /reservations/{id}`

### Yaklaşan Rezervasyonlar

**Endpoint**: `GET /reservations/upcoming?days=7`

### Takvim Görünümü

**Endpoint**: `GET /reservations/calendar?from=2026-02-01&to=2026-02-28`

---

## Ayarlar

### Ayarları Getir

**Endpoint**: `GET /settings`

### Ayarları Güncelle

**Endpoint**: `PUT /settings`

**İzin**: `settings.edit` (Admin)

```json
{
  "hotel_name": "Grand Hotel",
  "check_in_time": "15:00",
  "check_out_time": "11:00"
}
```

### Yedek Al

**Endpoint**: `POST /settings/backup`

**İzin**: `settings.backup` (Admin)

### Yedekten Geri Yükle

**Endpoint**: `POST /settings/restore`

```json
{
  "backup_file": "hotel_2026-02-18_120000.db"
}
```

### Yedekleri Listele

**Endpoint**: `GET /settings/backups`

---

## Dışa Aktarım

### Rezervasyonları CSV Olarak İhraç

**Endpoint**: `GET /export/reservations/csv?from=2026-02-01&to=2026-02-28`

### Müşterileri CSV Olarak İhraç

**Endpoint**: `GET /export/customers/csv`

### Odaları CSV Olarak İhraç

**Endpoint**: `GET /export/rooms/csv`

---

## Hata Kodları

| Kod | Anlam |
|-----|-------|
| 200 | Başarılı |
| 201 | Oluşturuldu |
| 400 | Hatalı İstek |
| 401 | Kimlik Doğrulaması Gerekli |
| 403 | Yetkisiz Erişim |
| 404 | Bulunamadı |
| 422 | Doğrulama Hatası |
| 500 | Sunucu Hatası |

---

## Örnek İş Akışı

```bash
# 1. Giriş yap
TOKEN=$(curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@hotel.local","password":"admin123"}' \
  | jq -r '.data.csrf_token')

# 2. Müsait odaları bul
curl "http://localhost:8000/api/rooms/available?check_in=2026-02-20&check_out=2026-02-22"

# 3. Müşteri oluştur
curl -X POST http://localhost:8000/api/customers \
  -H "Content-Type: application/json" \
  -d '{"first_name":"Ahmet","last_name":"Yılmaz","phone":"05551234567"}'

# 4. Rezervasyon oluştur
curl -X POST http://localhost:8000/api/reservations \
  -H "Content-Type: application/json" \
  -d '{"customer_id":1,"room_id":1,"check_in":"2026-02-20","check_out":"2026-02-22","number_of_guests":2}'

# 5. Check-in yap
curl -X POST http://localhost:8000/api/reservations/1/checkin

# 6. Check-out
curl -X POST http://localhost:8000/api/reservations/1/checkout \
  -d '{"total_price":1000}'
```

---

**Son Güncelleme**: Şubat 2026
