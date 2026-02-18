# Hotel Master Lite 🏨

**Açık Kaynaklı, Self-Hosted, Modern Otel Yönetim Sistemi**

[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php)](https://www.php.net/)
[![SQLite](https://img.shields.io/badge/Database-SQLite-003B57?logo=sqlite)](https://www.sqlite.org/)
[![PWA](https://img.shields.io/badge/Progressive-Web%20App-5A0FC2)](https://web.dev/progressive-web-apps/)

> Hotel Master Lite, butik otellerin tüm yönetim işlemlerini **kendi sunucularında**, **tamamen yerel verilerle** yapabilecekleri açık kaynaklı bir yazılımdır.

## ✨ Özellikler

### 🎯 MVP Özellikleri
- ✅ **Oda Yönetimi** - Oda ekleme, düzenleme, durum değiştirme
- ✅ **Rezervasyon Modülü** - Takvim görünümü, check-in/check-out
- ✅ **Müşteri Kartoteksi** - Müşteri bilgileri ve geçmiş konaklamalar
- ✅ **Gösterge Paneli** - Doluluk oranı, gelecek rezervasyonlar, hızlı istatistikler
- ✅ **Kullanıcı Yönetimi** - Rol tabanlı erişim (Admin, Resepsiyon, Oda Temizliği)
- ✅ **Raporlar & İhraç** - CSV ve PDF formatında dışa aktarım
- ✅ **Güvenlik** - Şifreleme, SQL Injection koruması, XSS koruması
- ✅ **PWA Desteği** - Offline çalışma, mobil uygulama gibi

## 🛠 Teknoloji Yığını

| Bileşen | Teknoloji |
|---------|-----------|
| **Backend** | Saf PHP 8.2+ (MVC-style, dependency-free) |
| **Veritabanı** | SQLite (dosya tabanlı, kurulum gerektirmez) |
| **Frontend** | HTML5, CSS3 (Flexbox/Grid), Vanilla JS (ES6+) |
| **Mobil** | PWA (Progressive Web App) |
| **Containerization** | Docker & Docker Compose |

## 📋 Sistem Gereksinimleri

- **PHP 8.2+** (SQLite3 uzantısı)
- **Linux/Unix** sunucusu (Ubuntu, CentOS, Debian, vb.)
- **Minimum 2GB RAM**
- **Minimum 1GB Disk Alanı**
- Modern web tarayıcı (Chrome, Firefox, Safari, Edge)

## 🚀 Hızlı Başlangıç

### Seçenek 1: Linux Kurulumu (En Kolay)

```bash
# Depo klonla
git clone https://github.com/yamanfurkan353-eng/lumina.git
cd lumina

# Kurulum script'ini çalıştır
chmod +x install.sh
./install.sh

# Sunucuyu başlat
php -S localhost:8000 -t public/

# Tarayıcıda aç
# http://localhost:8000
```

### Seçenek 2: Docker ile (Üretim İçin)

```bash
# Docker Compose ile başlat
docker-compose up -d

# Erişim adresi
# http://localhost

# Loglar
docker-compose logs -f
```

### Seçenek 3: Manuel Kurulum

1. **Repoyu klonla**
   ```bash
   git clone https://github.com/yamanfurkan353-eng/lumina.git
   cd lumina
   ```

2. **Klasörleri oluştur**
   ```bash
   mkdir -p database storage/{logs,exports,backups,uploads}
   chmod -R 755 storage
   ```

3. **Veritabanını başlat**
   ```bash
   sqlite3 database/hotel.db < database/schema.sql
   chmod 644 database/hotel.db
   ```

4. **Web sunucusunu yapılandır**
   - DocumentRoot: `/path/to/lumina/public`
   - PHP-FPM veya mod_php kullan

5. **Tarayıcıda aç**
   - Giriş sayfasında "-Kurulum Sihirbazı'ndan geçeceksiniz

## 📱 Kullanıcı Rolleri ve İzinler

### 👨‍💼 **Yönetici (Admin)**
- Tüm sistem erişimi
- Kullanıcı yönetimi
- Oda ve Rezervasyon tam kontrol
- Yedek alma/geri yükleme
- Ayar değiştirilmesi

### 👨‍💼 **Resepsiyon (Receptionist)**
- Rezervasyon yönetimi (tümü)
- Check-in/Check-out
- Müşteri yönetimi
- Oda durumunu görüntüleme
- Raporları görüntüleme

### 🧹 **Oda Temizliği (Housekeeping)**
- Oda durumunu güncelleme
- Rezervasyonları görüntüleme (salt okunur)

## 📖 Dokümantasyon

- [API Belgeleri](docs/API.md) - Tüm API endpoint'leri
- [Veritabanı Şeması](docs/DATABASE.md) - Tablo yapıları ve ilişkileri
- [Geliştirme Rehberi](docs/DEVELOPMENT.md) - Lokal geliştirme ortamı
- [Mimari](docs/ARCHITECTURE.md) - Sistem tasarımı

## 🔒 Güvenlik Özellikleri

- ✅ **SQL Injection Koruması** - Prepared Statements kullanılır
- ✅ **XSS Koruması** - HTML escape, CSP headers
- ✅ **CSRF Koruması** - Token doğrulaması
- ✅ **Şifre Güvenliği** - bcrypt hashing (cost: 12)
- ✅ **Oturum Yönetimi** - HttpOnly, Secure cookies
- ✅ **Rate Limiting** - DDOS koruması
- ✅ **Denetim Günlüğü** - Kullanıcı işlemlerinin kaydedilmesi

## 📊 API Örnekleri

### Giriş
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@hotel.local",
    "password": "admin123"
  }'
```

### Oda Listeleme
```bash
curl -X GET http://localhost:8000/api/rooms \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Rezervasyon Oluşturma
```bash
curl -X POST http://localhost:8000/api/reservations \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 1,
    "room_id": 1,
    "check_in": "2026-02-20",
    "check_out": "2026-02-22",
    "number_of_guests": 2
  }'
```

## 🗂 Proje Yapısı

```
lumina/
├── public/                 # Web root (DocumentRoot)
│   ├── index.php          # API giriş noktası
│   ├── login.html         # Giriş sayfası
│   ├── dashboard.html     # Ana kontrol paneli
│   ├── css/style.css      # Mobile-first responsive CSS
│   ├── js/                # JavaScript dosyaları
│   ├── manifest.json      # PWA manifest
│   └── sw.js              # Service Worker
├── src/                   # Uygulama kaynakları
│   ├── core/              # Temel sınıflar
│   ├── controllers/       # API controllers
│   ├── models/            # Veri modelleri
│   ├── middleware/        # Middleware
│   ├── utils/             # Yardımcı sınıflar
│   └── helpers/           # Yardımcı fonksiyonlar
├── database/              # Veritabanı
│   ├── schema.sql         # Tablo tanımları
│   └── hotel.db           # SQLite dosyası (.gitignore'da)
├── storage/               # Çalışma zamanı dosyaları
│   ├── logs/              # Uygulama logları
│   ├── exports/           # CSV/PDF dışa aktarımlar
│   ├── backups/           # Veritabanı yedekleri
│   └── uploads/           # Dosya yüklemeleri
├── config/                # Konfigürasyon dosyaları
├── docs/                  # Dokümantasyon
├── install.sh             # Linux kurulum script'i
├── Dockerfile             # Docker imajı
├── docker-compose.yml     # Docker Compose yapılandırması
└── README.md              # Bu dosya
```

## 🧪 Test Veri Yükleme

Kurulum sonrası demo veriler oluşturmak için:

```bash
php scripts/seed-demo-data.php
```

## 🔄 Veritabanı Yedekleme

Manuel yedekleme:
```bash
# Yedek oluştur
sqlite3 database/hotel.db ".backup backup_$(date +%Y%m%d_%H%M%S).db"

# Yedekten geri yükle
sqlite3 database/hotel.db ".restore backup_20260218_120000.db"
```

Veya API üzerinden:
```bash
curl -X POST http://localhost:8000/api/settings/backup
```

## 🌐 Üretim Dağıtımı

### Nginx Yapılandırması

[nginx.conf](nginx.conf) dosyasında verilen yapılandırmayı kullanın.

### SSL Sertifikası

```bash
# Let's Encrypt ile
sudo certbot certonly --nginx -d example.com
```

### Systemd Service

```bash
# /etc/systemd/system/hotel-master.service
[Unit]
Description=Hotel Master Lite
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/hotel-master
ExecStart=/usr/bin/php -S 127.0.0.1:9000 -t public
Restart=always

[Install]
WantedBy=multi-user.target
```

## 📈 Performans Optimizasyonu

- SQLite WAL modu etkin
- Veritabanı indeksleri
- CSS/JS minifikasyonu
- Gzip sıkıştırması
- Service Worker caching

## 🐛 Sorun Giderme

### "Veritabanı bağlantısı kurulamadı"
```bash
# Kontrol et
ls -la database/
chmod 755 database
chmod 644 database/hotel.db
```

### "Yazma izni yok"
```bash
chmod -R 755 storage/
```

### PHP uzantısı eksik
```bash
# Ubuntu/Debian
sudo apt-get install php-sqlite3
sudo systemctl restart php-fpm
```

## 📝 Lisans

Bu proje [Apache License 2.0](LICENSE) altında lisanslanmıştır.

## 🤝 Katkı Yapma

Katkılarınızı memnuniyetle karşılarız! Işık açınız (fork), değişiklik yapınız ve bir pull request gönderin.

## 📧 İletişim

- **E-posta**: [proje-maintainer@example.com](mailto:proje-maintainer@example.com)
- **GitHub Issues**: [Sorun Bildirin](https://github.com/yamanfurkan353-eng/lumina/issues)

## 🎯 Yol Haritası

- [ ] Eposta bildirimleri
- [ ] Gelişmiş raporlar
- [ ] Çok dil desteği
- [ ] Mobil uygulaması (React Native)
- [ ] API authentication tokens
- [ ] Redis caching
- [ ] Elasticsearch entegrasyonu

---

**Hotel Master Lite** ile otellerin yönetimini kolaylaştırın! 🚀

---

**Sürüm**: 1.0.0
**Son Güncelleme**: Şubat 2026
**Bakım Durumu**: Aktif Geliştirme
