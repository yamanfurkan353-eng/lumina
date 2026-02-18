# Dosya Yapısı - Hotel Master Lite

```
lumina/
├── 📄 .editorconfig           # Editor konfigürasyonu (tutarlılık)
├── 📄 .env.example            # Ortam değişkenleri template'i
├── 📄 .gitignore              # Git'ten hariç tutulacak dosyalar
├── 📄 Dockerfile              # Docker container görüntüsü
├── 📄 LICENSE                 # Apache 2.0 lisansı
├── 📄 README.md               # Ana belgeler ve hızlı başlangıç
├── 📄 docker-compose.yml      # Multi-container orchestration
├── 📄 nginx.conf              # Nginx web sunucusu konfigürasyonu
├── 📄 install.sh              # İnteraktif kurulum scripti (Bash)
├── 📄 PROJECT_STATUS.md       # Bu proje durum raporu
│
├── 📁 .github/
│   └── 📁 workflows/          # CI/CD (gelecek)
│
├── 📁 config/                 # ✅ Yapılandırma dosyaları
│   ├── constants.php          # 100+ sabit tanımları
│   ├── config.php             # Bootstrap & session setup
│   └── roles.php              # RBAC role tanımları
│
├── 📁 database/               # ✅ Veritabanı
│   ├── schema.sql             # DDL: 6 tablo, 11 dizin
│   ├── hotel.db               # SQLite dosyası (üretimde oluşturulur)
│   └── init.php               # Veritabanı başlatma scripti
│
├── 📁 src/                    # ✅ Kaynak kodu
│   │
│   ├── 📁 core/               # Çekirdek sınıflar
│   │   ├── Database.php       # Singleton PDO/SQLite (320 satır)
│   │   ├── Auth.php           # Kimlik doğrulama & RBAC (180 satır)
│   │   ├── Router.php         # RESTful routing (150 satır)
│   │   ├── Logger.php         # Günlükleme sistemi (120 satır)
│   │   └── Response.php       # JSON response formatlama (100 satır)
│   │
│   ├── 📁 controllers/        # API kontrolcüleri (7 dosya, 1500+ satır)
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── RoomController.php
│   │   ├── CustomerController.php
│   │   ├── ReservationController.php
│   │   ├── SettingsController.php
│   │   └── ExportController.php
│   │
│   ├── 📁 models/             # Veri modelleri (5 dosya)
│   │   ├── User.php           # Kullanıcı modeli
│   │   ├── Room.php           # Oda modeli
│   │   ├── Customer.php       # Müşteri modeli
│   │   ├── Reservation.php    # Rezervasyon modeli
│   │   └── Setting.php        # Ayarlar modeli
│   │
│   ├── 📁 utils/              # Yardımcı sınıflar
│   │   ├── Validator.php      # 15+ doğrulama metodu (250 satır)
│   │   └── FileManager.php    # Dosya operasyonları (200 satır)
│   │
│   ├── 📁 helpers/            # Yardımcı fonksiyonlar
│   │   └── DateHelper.php     # 20+ tarih/para fonksiyonları (200 satır)
│   │
│   └── 📁 middleware/         # Ara katman yazılımı (gelecek)
│
├── 📁 public/                 # ✅ Web root (İstemci erişilebilir)
│   │
│   ├── 📄 index.php           # API entry point & router (100 satır)
│   ├── 📄 login.html          # Giriş sayfası
│   ├── 📄 setup.html          # Kurulum sihirbazı
│   ├── 📄 dashboard.html      # Gösterge paneli (İstatistikler)
│   ├── 📄 rooms.html          # Oda yönetim sayfası
│   ├── 📄 reservations.html   # Rezervasyon yönetim sayfası
│   ├── 📄 customers.html      # Müşteri yönetim sayfası
│   ├── 📄 settings.html       # Sistem ayar sayfası
│   ├── 📄 manifest.json       # PWA manifest
│   ├── 📄 sw.js               # Service Worker (120 satır, offline support)
│   │
│   ├── 📁 css/                # ✅ Stil sayfaları
│   │   └── style.css          # Base styles (600+ satır, responsive)
│   │
│   ├── 📁 js/                 # ✅ JavaScript
│   │   ├── app.js             # App initialization (70 satır)
│   │   └── pwa.js             # PWA registration (25 satır)
│   │
│   ├── 📁 images/             # Resim ve ikonlar
│   │   ├── logo.svg           # Logo (SVG)
│   │   └── icons/             # App ikonları (192x192, 512x512)
│   │
│   └── 📁 views/              # HTML template'leri (opsiyonel)
│
├── 📁 storage/                # ✅ Dinamik dosyalar
│   │
│   ├── 📁 logs/               # Uygulama günlükleri
│   │   ├── error.log          # Hata günlüğü
│   │   ├── info.log           # Bilgi günlüğü
│   │   ├── debug.log          # Debug günlüğü
│   │   └── audit.log          # Denetim günlüğü
│   │
│   ├── 📁 exports/            # Dışa aktarılan dosyalar
│   │   └── *.csv              # Dinamik olarak oluşturulan CSV'ler
│   │
│   ├── 📁 backups/            # Veritabanı yedekleri
│   │   └── hotel_*.db         # Zaman damgalı yedekler
│   │
│   └── 📁 uploads/            # Kullanıcı yükleme alanı
│
├── 📁 docs/                   # ✅ Kapsamlı belgeler (Türkçe)
│   ├── API.md                 # API dokumentasyon (400+ satır)
│   │                          # • Tüm 25+ endpoint açıklaması
│   │                          # • cURL örnekleri
│   │                          # • Hata kodları
│   │                          # • İş akışı örnekleri
│   │
│   ├── DATABASE.md            # Veritabanı şeması (300+ satır)
│   │                          # • Tablo tanımları
│   │                          # • SQL sorgu örnekleri
│   │                          # • Normalizasyon notları
│   │
│   ├── DEVELOPER.md           # Geliştirici rehberi (500+ satır)
│   │                          # • Proje yapısı
│   │                          # • Mimarı açıklaması
│   │                          # • Yeni endpoint ekleme
│   │                          # • Best practices
│   │
│   ├── ROLES.md               # Kullanıcı rolleri (300+ satır)
│   │                          # • Admin, Resepsiyon, Temizlik rolleri
│   │                          # • İzin matrisi
│   │                          # • Özel senaryolar
│   │
│   └── QUICKSTART.md          # Hızlı başlangıç (500+ satır)
│                              # • Docker kurulum
│                              # • Lokal kurulum
│                              # • VPS kurulum
│                              # • İlk adımlar
│
├── 📁 tests/                  # ✅ Testler
│   │
│   ├── 📁 unit/               # Birim testleri
│   │   ├── DatabaseTest.php   # Database sınıf testleri
│   │   ├── ValidatorTest.php  # Validator testleri
│   │   └── ...
│   │
│   └── 📁 integration/        # Entegrasyon testleri
│       ├── ReservationFlowTest.php
│       ├── AuthFlowTest.php
│       └── ...

---

## 📊 Topla İstatistikler

```
DOSYA SAYILARI:
├── Backend PHP:           15+ dosya
├── Frontend:              10+ dosya
├── Konfigürasyon:         5+ dosya
├── Belgeler:              6+ dosya
├── Dağıtım:              3+ dosya
└── TOPLAM:               39+ dosya

KOD SATIR SAYILARI:
├── PHP Backend:           ~2,500+ satır
├── Frontend (HTML/CSS/JS):~1,200+ satır
├── Kurulum & Deploy:      ~400+ satır
├── Belgeler:              ~2,000+ satır
└── TOPLAM:                ~6,000+ satır

TEKNOLOJİ:
├── Programlama Dili:      PHP 8.2+
├── Veritabanı:            SQLite3
├── Frontend:              HTML5, CSS3, JavaScript ES6+
├── Dağıtım:               Docker, Docker Compose, Bash
├── Web Sunucusu:          Nginx, Apache
└── Dış Bağımlılıklar:     0 (Sıfır!)
```

---

## 🔍 Dosya Açıklamaları

### Yapılandırma dosyaları
| Dosya | Amaç |
|-------|------|
| `.editorconfig` | IDE'lerde tutarlı indentation/LF |
| `.env.example` | Ortam değişkenleri template'i |
| `.gitignore` | Git'ten hariç tutulan dosyalar |
| `config/constants.php` | Sabit tanımları (statüsler, roller, vb) |
| `config/config.php` | Başlangıç kodu, session yapılandırması |
| `config/roles.php` | RBAC izin matrisi |

### Çekirdek sistem
| Dosya | Satır | Amaç |
|-------|--------|------|
| `Database.php` | 320+ | SQLite bağlantı & sorgular |
| `Auth.php` | 180+ | Giriş, session, RBAC |
| `Router.php` | 150+ | API route yönetimi |
| `Logger.php` | 120+ | 4 seviye günlükleme |
| `Response.php` | 100+ | JSON response formatı |

### Kontrolcüler (API Endpoints)
| Kontrol | Satır | Endpoints |
|---------|-------|-----------|
| `AuthController` | 80+ | login, user, logout, change-password |
| `DashboardController` | 60+ | dashboard stats |
| `RoomController` | 200+ | rooms CRUD + available |
| `CustomerController` | 150+ | customers CRUD + search |
| `ReservationController` | 250+ | reservations CRUD + checkin/out |
| `SettingsController` | 180+ | settings + backup/restore |
| `ExportController` | 120+ | CSV export |

### Frontend sayfaları
| Sayfa | Amaç | Özellikler |
|-------|----|----|
| `login.html` | Kullanıcı girişi | AJAX auth, remember |
| `setup.html` | İlk kurulum | 3 adımlı wizard |
| `dashboard.html` | Ana sayfa | Stats, charts, today's bookings |
| `rooms.html` | Oda yönetimi | CRUD, status change, pagination |
| `reservations.html` | Rezervasyon | CRUD, check-in/out, calendar |
| `customers.html` | Müşteri | CRUD, search, details modal |
| `settings.html` | Ayarlar | Config, users, backup/restore |

---

## 🚀 Başlama Dizin Ağacı

```bash
# Docker ile başla (Tavsiye edilen)
docker-compose up -d

# Veya lokal PHP sunucusu ile
php -S localhost:8000 -t public/

# Veya VPS'e manuel kurulum
./install.sh
```

Tüm dosyalar production-ready ve fully documented! 🎉

---

**Son Güncelleme**: Şubat 2026
