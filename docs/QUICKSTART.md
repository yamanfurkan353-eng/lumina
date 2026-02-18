# Hızlı Başlangıç - Hotel Master Lite

Bu rehber, Hotel Master Lite'ı sadece **5 dakika** içinde kurmanıza yardımcı olacak.

## ⚡ En Hızlı Yol: Docker

### 1 Dakika Kurulum

```bash
# 1. Repository'yi indir
git clone https://github.com/yourusername/lumina.git
cd lumina

# 2. Başlat ve erişim
docker-compose up -d

# 3. Tarayıcıda aç
http://localhost:8000
```

**İşte bu!** ✅ Sistem çalışıyor.

---

## 💻 Lokal Kurulum (PHP Yüklüysə)

### Gereksinimler
- PHP 8.2+
- SQLite3 aktivə edilmiş

### Kurulum

```bash
# 1. İndir
git clone https://github.com/yourusername/lumina.git
cd lumina

# 2. Çalıştır
php -S localhost:8000 -t public/
```

Tarayıcı otomatik açılacak: `http://localhost:8000`

---

## 🛠️ VPS/VDS'e Kurulum (Üretim)

### Gereksinimler
- Ubuntu 20.04+ LTS
- SSH erişimi
- Root veya sudo hakkı

### Kurulum Adımları

```bash
# 1. VPS'ye bağlan
ssh root@SUNUCUIP

# 2. Sistemi güncelle
apt update && apt upgrade -y

# 3. Repository'yi indir
cd /var/www
git clone https://github.com/yourusername/lumina.git hotel
cd hotel

# 4. Kurulum scriptini çalıştır
chmod +x install.sh
./install.sh
```

### İnteraktif Kurulum Soruları

```
→ Otel Adı: [Otelin Adını Gir]
→ Para Birimi: [TRY]
→ Yönetici E-postası: admin@otel.local
→ Yönetici Şifresi: [Güçlü bir şifre gir]
→ Check-in Saati: 14:00
→ Check-out Saati: 11:00
```

### Nginx Konfigürasyonu

```bash
# Nginx virtual host oluştur
sudo nano /etc/nginx/sites-available/hotel

# Aşağıdaki kodu yapıştır:
server {
    listen 80;
    server_name otel.example.com;
    root /var/www/hotel/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}

# Etkinleştir
sudo ln -s /etc/nginx/sites-available/hotel /etc/nginx/sites-enabled/
sudo systemctl reload nginx
```

### SSL Sertifikası (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d otel.example.com
```

---

## 🔐 İlk Kullanım

### 1. Giriş
1. Tarayıcıda `http://localhost:8000` aç
2. **E-posta**: `admin@hotel.local`
3. **Şifre**: Kurulum sırasında belirlediğin şifre
4. **Giriş Yap** butonuna tıkla

### 2. Hoş Geldiniz!
- 📊 **Gösterge Paneli** → İstatistikler
- 🛏️ **Odalar** → Oda ekle
- 📅 **Rezervasyonlar** → Rezervasyon oluş
- 👥 **Müşteriler** → Müşteri yönetimi
- ⚙️ **Ayarlar** → Sistem ayarları

### 3. İlk Adımlar

**Adım 1: Odaları Ekle**
```
Odalar → + Yeni Oda
- Oda Numarası: 101, 102, vb.
- Tipi: Double, Single, Suite
- Kapasite: 2 (yatak sayısı)
- Gecelik Fiyat: 500 ₺
- Kat: 1
```

**Adım 2: Müşteri Ekle**
```
Müşteriler → + Yeni Müşteri
- Ad: Ahmet
- Soyad: Yılmaz
- Telefon: 05551234567
- E-posta: ahmet@example.com
```

**Adım 3: Rezervasyon Oluştur**
```
Rezervasyonlar → + Yeni Rezervasyon
- Müşteri: Ahmet Yılmaz seç
- Oda: 101 seç
- Check-in: Yarın
- Check-out: İki gün sonra
- Konuklar: 2
```

**Adım 4: Check-in**
```
Müşteri gelince:
- Rezervasyonlar sayfasında "Check-in" butonuna tıkla
- Sistem otomatik olarak oda durumunu "Dolu" yapar
```

---

## 📱 Mobil Erişim

Sistem, mobil telefonlardan tam olarak çalışır!

```
Telefonda açın: http://SUNUCUIP:8000
```

Uygulamayı ev ekranına ekle:
- **Android**: Üç nokta → "Ev ekranına ekle"
- **iPhone**: Paylaş → "Ev Ekranına Ekle"

---

## 🆘 Sorun Giderme

### "Sayfanız görünmüyor"
```bash
# PHP 8.2+ var mı?
php -v

# SQLite3 etkinleştirilmiş mi?
php -m | grep sqlite3

# Veritabanı var mı?
ls -la database/hotel.db
```

### "Veritabanı hatası"
```bash
# İzinleri düzelt
chmod 755 database/
chmod 666 database/hotel.db
chmod -R 755 storage/
```

### "404 Hatası"
- `.htaccess` etkinleştirilmiş mi? (Apache için)
- Nginx `try_files` yapılandırması doğru mu?

### "Çok yavaş"
- SQLite sorgularını optimize et
- Cache etkinleştir (Redis)
- CDN kullan (static files)

---

## 🔒 Güvenlik Kontrol Listesi

Üretim ortamına geçmeden:

- [ ] **Güçlü şifre** belirle (Yönetici)
- [ ] **SSL sertifikası** yükle (HTTPS)
- [ ] **Firewall** kuralları ayarla (80, 443, 22 portları)
- [ ] **Otomatik yedekler** yapılandır
- [ ] **Günlükleri** kontrol et
- [ ] **İzinleri** sıfırla (`chmod 755`)
- [ ] **`.git` klasörünü** sakla (`.htaccess` veya Nginx rules)
- [ ] **`storage/` dizinine** web erişimini engelle

---

## 📊 En Sık Kullanılan İşlemler

### Günlük Check-in/Check-out
```
1. Gösterge Paneli → Bugünün Check-inleri
2. "Check-in" butonuna tıkla
3. İşlem tamamlandı!
```

### Yeni Rezervasyon
```
Rezervasyonlar → + Yeni Rezervasyon
→ Müşteri seç → Oda seç → Tarihler → Oluştur
```

### Müşteri Arama
```
Müşteriler → Arama kutusuna yazı gir
→ Sonuçlar otomatik gösterilir
```

### Yedek Alma
```
Ayarlar → Yedek & Geri Yükleme → 💾 Yedek Al
→ Sistem otomatik yedek oluşturur
```

---

## 🚀 Gelecek Adımlar

1. **Raporlar**: İstatistik raporları oluştur
2. **E-posta Bildirimleri**: Otomatik rezervasyon e-postaları
3. **Mobil Uygulama**: Native iOS/Android uygulaması
4. **Ödeme Entegrasyonu**: Online ödeme sistemi
5. **Channel Manager**: Booking.com, Airbnb entegrasyonu

---

## 📞 Destek

Sorunuz mu var?
- **E-posta**: support@hotelmasterlite.local
- **GitHub Issues**: https://github.com/yourusername/lumina/issues
- **Wiki**: https://wiki.hotelmasterlite.local

---

**Hoş geldiniz! Hotel Master Lite'ı kullandığınız için teşekkür ederiz!** 🎉

---

**Son Güncelleme**: Şubat 2026
