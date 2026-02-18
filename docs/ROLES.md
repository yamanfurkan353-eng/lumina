# Kullanıcı Rolleri ve İzinleri - Hotel Master Lite

Bu dokümantasyon rol tabanlı erişim kontrolü (RBAC) hakkında bilgi verir.

## Roller

Hotel Master Lite'da 3 temel rol bulunmaktadır:

### 1. 👨‍💼 Yönetici (Admin)

**Kullanım Durumu**: Otel müdürü, teknik sorumlu

**Erişim Alanları**:
- ✅ Tüm özellikler
- ✅ Kullanıcı yönetimi
- ✅ Sistem ayarları
- ✅ Yedek & geri yükleme
- ✅ Denetim günlükleri
- ✅ Raporlar

**İzinler** (25+):
| İzin | Açıklama |
|------|----------|
| `users.view` | Kullanıcıları görüntüle |
| `users.create` | Yeni kullanıcı oluştur |
| `users.edit` | Kullanıcı düzenle |
| `users.delete` | Kullanıcı sil |
| `rooms.view` | Odaları görüntüle |
| `rooms.create` | Yeni oda ekle |
| `rooms.edit` | Oda bilgisini düzenle |
| `rooms.delete` | Oda sil |
| `reservations.view` | Rezervasyonları görüntüle |
| `reservations.create` | Yeni rezervasyon oluştur |
| `reservations.edit` | Rezervasyon düzenle |
| `reservations.cancel` | Rezervasyon iptal et |
| `customers.view` | Müşterileri görüntüle |
| `customers.create` | Yeni müşteri ekle |
| `customers.edit` | Müşteri bilgisini düzenle |
| `customers.delete` | Müşteri sil |
| `settings.view` | Ayarları görüntüle |
| `settings.edit` | Ayarları değiştir |
| `settings.backup` | Yedek al |
| `settings.restore` | Yedekten geri yükle |
| `export.data` | Veri dışa aktarım |
| `logs.view` | Günlükleri görüntüle |
| `reports.view` | Raporları görüntüle |
| `dashboard.view` | Gösterge panelini görüntüle |

---

### 2. 👨‍💻 Resepsiyon (Receptionist)

**Kullanım Durumu**: Ön büro çalışanları, rezervasyon görevlileri

**Erişim Alanları**:
- ✅ Rezervasyon yönetimi (CRUD)
- ✅ Müşteri yönetimi (CRUD)
- ✅ Oda durumu görüntüleme
- ✅ Check-in/Check-out işlemleri
- ✅ Basit raporlar
- ❌ Oda ekleme/silme
- ❌ Kullanıcı yönetimi
- ❌ Sistem ayarları
- ❌ Yedek işlemleri

**İzinler** (14):
```
reservations.view
reservations.create
reservations.edit
reservations.checkin
reservations.checkout
customers.view
customers.create
customers.edit
rooms.view
export.data
dashboard.view
logs.view (limited)
reports.view
settings.view
```

**Check-in Akışı**:
1. Müşteriye hoş geldiniz söyle
2. Rezervasyonu sisteme yükle
3. Kimlik fotoğrafla
4. Check-in butonuna bas
5. Oda anahtarını ver

**Check-out Akışı**:
1. Check-out saatini kontrol et
2. Oda konumunu doğrula
3. Oda durumunu "temizlenecek" olarak işaretle
4. Check-out işlemini tamamla
5. Ödemeyi al

---

### 3. 🧹 Oda Temizliği (Housekeeping)

**Kullanım Durumu**: Oda temizlik görevlileri, yardımcı personel

**Erişim Alanları**:
- ✅ Oda durumu görüntüleme
- ✅ Oda durumunu güncelleme (dirty → available)
- ✅ Kişisel görev portalı
- ❌ Rezervasyonları yönetme
- ❌ Müşteri bilgisini değiştirme
- ❌ Ödeme işlemleri
- ❌ Sistem ayarları

**İzinler** (6):
```
rooms.view
rooms.editstatus
reservations.view (limited - sadece atanmış odalar)
dashboard.view (limited - sadece görevleri)
logs.view
```

**Görev Akışı**:
1. Uygulamayı aç → Temizleme Görevlerim
2. Temizlenecek odaları gör
3. Her odaya git ve temizle
4. Uygulamada "Temizlendi" işaretle
5. Yönetici tarafından kontrol edilmesini bekle

---

## İzin Matrisi

| İşlem | Admin | Resepsiyon | Housekeeping |
|-------|-------|-----------|--------------|
| **Oda Yönetimi** | | | |
| Oda ekle/sil | ✅ | ❌ | ❌ |
| Oda bilgisini düzenle | ✅ | ❌ | ❌ |
| Oda durumunu değiştir | ✅ | ✅ | ✅ |
| Oda görüntüle | ✅ | ✅ | ✅ |
| **Rezervasyon** | | | |
| Rezervasyon ekle | ✅ | ✅ | ❌ |
| Rezervasyon düzenle | ✅ | ✅ | ❌ |
| Rezervasyon iptal | ✅ | ✅ | ❌ |
| Check-in/Check-out | ✅ | ✅ | ❌ |
| Rezervasyon görüntüle | ✅ | ✅ | ✅ |
| **Müşteri** | | | |
| Müşteri ekle/sil | ✅ | ✅ | ❌ |
| Müşteri bilgisini düzenle | ✅ | ✅ | ❌ |
| Müşteri görüntüle | ✅ | ✅ | ❌ |
| **Sistem** | | | |
| Kullanıcı yönetimi | ✅ | ❌ | ❌ |
| Sistem ayarları | ✅ | ❌ | ❌ |
| Yedek/Geri yükleme | ✅ | ❌ | ❌ |
| Günlükleri görüntüle | ✅ | ✅ | ❌ |
| Raporlar | ✅ | ✅ | ❌ |

---

## Rol Atama

### Admin Kullanıcı Oluştur (Kurulum Sırasında)
```bash
php install.sh
# İstemde: "Yönetici E-postası" ve "Yönetici Şifresi" gir
```

### Diğer Kullanıcılar Ekle
1. Ayarlar → Kullanıcı Yönetimi → "Yeni Kullanıcı"
2. Ad, E-posta, Şifre gir
3. Rol seç: Admin / Resepsiyon / Oda Temizliği
4. Kaydet

---

## İzin Kontrolü (Teknik)

### PHP'de İzin Kontrol

```php
use App\Core\Auth;

// Tek izin kontrol
if (!Auth::hasPermission('reservations.create')) {
    Response::forbidden('Bu işleme izniniz yok');
}

// Birden fazla izinden birini kontrol
if (!Auth::hasAnyPermission(['reservations.create', 'reservations.edit'])) {
    Response::forbidden('Gerekli izniniz yok');
}

// Tüm izinleri kontrol
Auth::requirePermission('users.delete'); // Hata varsa 403 döner
```

### Ve Request'te (JavaScript)

```javascript
// Öğe göster/gizle
if (app.hasPermission('users.create')) {
    document.getElementById('addUserBtn').style.display = 'block';
}
```

---

## En İyi Uygulamalar

### 1. Minimum İzin Prensibesi
> Her kullanıcı yalnızca işini yapmak için gerekli izinlere sahip olsun.

```
❌ Temizlik görevlisine admin izni verme
✅ Yalnızca "rooms.view" ve "rooms.editstatus" izni ver
```

### 2. İzin Denetimi
> Her API endpoint'inde izin kontrol et

```php
// Kontrol YAPILMAYAN (kötü)
public function deleteUser($id) {
    User::delete($id);
}

// Kontrol YAPILAN (iyi)
public function deleteUser($id) {
    Auth::requirePermission('users.delete');
    User::delete($id);
}
```

### 3. Denetim Günlüğü
> Hassas işlemlerin günlüğünü kaydımız 

```php
Logger::audit(
    'user_deleted',
    'User',
    $userId,
    ['admin_id' => Auth::user()['id']]
);
```

---

## Özel Senaryolar

### Müdür Yoksa Resepsiyon Admin İşlemleri Yapabilir mi?
**Hayır**, Admin yalnızca Auth sistem tarafından atanmış Admin rolle yapabilir. Müdürün rolratüyle yeniden yapılandırılması gerekir.

### Sadece Atanmış Odaları Görebilen Temizlik Görevlisi
Şu anda desteklenmiyor, gelecek sürümde eklenebilir:
```
housekeeping.view_assigned_only
```

### Admin Olmayan Yedek Oluşturabilir mi?
**Hayır**, sadece Admin `settings.backup` izinli olmalı.

---

## Sorun Giderme

### "Yetkisiz Erişim" Hatası
- Kullanıcının doğru rolü var mı?
- Rol için gerekli izin yapılandırıldı mı?
- Session hâlâ aktif midir?

### Beklenmeyen İzin Verme
- `config/roles.php`'de rol tanımını kontrol et
- Denetim günlüğünde kim tarafından değiştirildiğini gör

---

**Son Güncelleme**: Şubat 2026
