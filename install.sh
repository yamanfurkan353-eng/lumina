#!/bin/bash

# ============================================
# Hotel Master Lite Installation Script
# Bash script for Linux/Unix installations
# ============================================

set -e

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   Hotel Master Lite - Setup Wizard    ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"
echo ""

# ============================================
# System Requirements Check
# ============================================

echo -e "${YELLOW}✓ Sistem gereksinimlerini kontrol ediliyoruz...${NC}"

# Check PHP version
PHP_VERSION=$(php -v | head -n 1 | grep -oP '(?<=PHP )\d+\.\d+')
PHP_MAJOR=$(echo $PHP_VERSION | cut -d. -f1)
PHP_MINOR=$(echo $PHP_VERSION | cut -d. -f2)

if [[ $PHP_MAJOR -lt 8 ]] || [[ $PHP_MAJOR -eq 8 && $PHP_MINOR -lt 2 ]]; then
    echo -e "${RED}✗ Hata: PHP 8.2 veya daha yüksek sürüm gereklidir (Mevcut: $PHP_VERSION)${NC}"
    exit 1
fi

echo -e "${GREEN}✓ PHP sürümü uygun: $PHP_VERSION${NC}"

# Check SQLite PHP extension
if ! php -m | grep -q sqlite3; then
    echo -e "${RED}✗ Hata: PHP SQLite3 uzantısı yüklü değil${NC}"
    echo -e "${YELLOW}  Kurulum: sudo apt-get install php-sqlite3${NC}"
    exit 1
fi

echo -e "${GREEN}✓ PHP SQLite3 uzantısı kontrol edildi${NC}"

# Check write permissions
if [[ ! -w "$SCRIPT_DIR" ]]; then
    echo -e "${RED}✗ Hata: Yazma izni yok: $SCRIPT_DIR${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Klasör yazma izni kontrol edildi${NC}"
echo ""

# ============================================
# Directory Setup
# ============================================

echo -e "${YELLOW}✓ Klasörler oluşturuluyor...${NC}"

mkdir -p "$SCRIPT_DIR/database"
mkdir -p "$SCRIPT_DIR/storage/logs"
mkdir -p "$SCRIPT_DIR/storage/exports"
mkdir -p "$SCRIPT_DIR/storage/backups"
mkdir -p "$SCRIPT_DIR/storage/uploads"

chmod 755 "$SCRIPT_DIR/storage"
chmod 755 "$SCRIPT_DIR/storage/logs"
chmod 755 "$SCRIPT_DIR/storage/exports"
chmod 755 "$SCRIPT_DIR/storage/backups"
chmod 755 "$SCRIPT_DIR/storage/uploads"

echo -e "${GREEN}✓ Klasörler oluşturuldu${NC}"
echo ""

# ============================================
# Database Initialization
# ============================================

echo -e "${YELLOW}✓ Veritabanı başlatılıyor...${NC}"

DATABASE_FILE="$SCRIPT_DIR/database/hotel.db"

if [[ -f "$DATABASE_FILE" ]]; then
    echo -e "${YELLOW}  Veritabanı zaten mevcut. Devam edilsin mi? (E/H)${NC}"
    read -p "  " CONTINUE
    if [[ "$CONTINUE" != "E" && "$CONTINUE" != "e" ]]; then
        echo -e "${RED}Kurulum iptal edildi${NC}"
        exit 1
    fi
else
    # Create database from schema
    sqlite3 "$DATABASE_FILE" < "$SCRIPT_DIR/database/schema.sql"
    chmod 644 "$DATABASE_FILE"
    echo -e "${GREEN}✓ Veritabanı oluşturuldu${NC}"
fi

echo ""

# ============================================
# Interactive Configuration
# ============================================

echo -e "${BLUE}════════════════════════════════════════${NC}"
echo -e "${BLUE}   1. ADIM: Otel Bilgileri${NC}"
echo -e "${BLUE}════════════════════════════════════════${NC}"
echo ""

read -p "Otel Adı [Hotel Master Lite]: " HOTEL_NAME
HOTEL_NAME=${HOTEL_NAME:-"Hotel Master Lite"}

echo ""
echo "Para Birimi Seçin:"
echo "  1) Türk Lirası (₺) - DEFAULT"
echo "  2) Euro (€)"
echo "  3) Amerikan Doları (\$)"

read -p "Seçim (1-3) [1]: " CURRENCY_CHOICE
CURRENCY_CHOICE=${CURRENCY_CHOICE:-"1"}

case $CURRENCY_CHOICE in
    1) CURRENCY="TRY"; CURRENCY_SYMBOL="₺" ;;
    2) CURRENCY="EUR"; CURRENCY_SYMBOL="€" ;;
    3) CURRENCY="USD"; CURRENCY_SYMBOL="\$" ;;
    *) CURRENCY="TRY"; CURRENCY_SYMBOL="₺" ;;
esac

echo ""

# Admin user setup
echo -e "${BLUE}════════════════════════════════════════${NC}"
echo -e "${BLUE}   2. ADIM: Yönetici Hesabı${NC}"
echo -e "${BLUE}════════════════════════════════════════${NC}"
echo ""

read -p "Yönetici Adı Soyadı: " ADMIN_NAME

while [[ -z "$ADMIN_EMAIL" ]]; do
    read -p "Yönetici E-posta: " ADMIN_EMAIL
    if [[ ! "$ADMIN_EMAIL" =~ ^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$ ]]; then
        echo -e "${RED}Geçersiz e-posta adresi${NC}"
        ADMIN_EMAIL=""
    fi
done

while [[ -z "$ADMIN_PASSWORD" ]]; do
    read -sp "Yönetici Şifresi (min 8 karakter): " ADMIN_PASSWORD
    echo ""
    
    if [[ ${#ADMIN_PASSWORD} -lt 8 ]]; then
        echo -e "${RED}Şifre çok kısa. Minimum 8 karakter gerekli${NC}"
        ADMIN_PASSWORD=""
    fi
done

read -sp "Şifre Tekrarı: " ADMIN_PASSWORD_CONFIRM
echo ""

while [[ "$ADMIN_PASSWORD" != "$ADMIN_PASSWORD_CONFIRM" ]]; do
    echo -e "${RED}Şifreler eşleşmiyor${NC}"
    read -sp "Yönetici Şifresi: " ADMIN_PASSWORD
    echo ""
    read -sp "Şifre Tekrarı: " ADMIN_PASSWORD_CONFIRM
    echo ""
done

echo ""

# Check-in/Check-out times
echo -e "${BLUE}════════════════════════════════════════${NC}"
echo -e "${BLUE}   3. ADIM: Zaman Ayarları${NC}"
echo -e "${BLUE}════════════════════════════════════════${NC}"
echo ""

read -p "Check-in Saati (HH:MM) [14:00]: " CHECK_IN_TIME
CHECK_IN_TIME=${CHECK_IN_TIME:-"14:00"}

read -p "Check-out Saati (HH:MM) [11:00]: " CHECK_OUT_TIME
CHECK_OUT_TIME=${CHECK_OUT_TIME:-"11:00"}

echo ""

# ============================================
# Database Configuration
# ============================================

# Hash admin password using PHP
ADMIN_HASH=$(php -r "echo password_hash('$ADMIN_PASSWORD', PASSWORD_BCRYPT, ['cost' => 12]);")

# Update settings in database
php <<EOF
<?php
require_once '$SCRIPT_DIR/config/config.php';
require_once '$SCRIPT_DIR/config/constants.php';

try {
    \$db = new PDO("sqlite:$DATABASE_FILE");
    
    // Update settings
    \$db->exec("UPDATE settings SET value = '$HOTEL_NAME' WHERE key = 'hotel_name'");
    \$db->exec("UPDATE settings SET value = '$CURRENCY' WHERE key = 'currency'");
    \$db->exec("UPDATE settings SET value = '$CURRENCY_SYMBOL' WHERE key = 'currency_symbol'");
    \$db->exec("UPDATE settings SET value = '$CHECK_IN_TIME' WHERE key = 'check_in_time'");
    \$db->exec("UPDATE settings SET value = '$CHECK_OUT_TIME' WHERE key = 'check_out_time'");
    
    // Insert admin user
    \$sql = "INSERT INTO users (name, email, password_hash, role, is_active, created_at, updated_at) 
             VALUES (?, ?, ?, ?, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
    \$stmt = \$db->prepare(\$sql);
    \$stmt->execute([\$_SERVER['ADMIN_NAME'], \$_SERVER['ADMIN_EMAIL'], \$_SERVER['ADMIN_HASH'], 'admin']);
    
    echo "OK";
} catch (PDOException \$e) {
    echo "ERROR: " . \$e->getMessage();
    exit(1);
}
?>
EOF

ADMIN_NAME="$ADMIN_NAME" ADMIN_EMAIL="$ADMIN_EMAIL" ADMIN_HASH="$ADMIN_HASH" \
php -r "
require_once '$SCRIPT_DIR/config/config.php';
\$db = new PDO('sqlite:$DATABASE_FILE');

// Insert admin user
\$sql = 'INSERT OR REPLACE INTO users (name, email, password_hash, role, is_active, created_at, updated_at) 
         VALUES (?, ?, ?, ?, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)';
\$stmt = \$db->prepare(\$sql);
\$stmt->execute([\$_ENV['ADMIN_NAME'], \$_ENV['ADMIN_EMAIL'], \$_ENV['ADMIN_HASH'], 'admin']);
" ADMIN_NAME="$ADMIN_NAME" ADMIN_EMAIL="$ADMIN_EMAIL" ADMIN_HASH="$ADMIN_HASH"

echo ""

# ============================================
# Configuration File
# ============================================

echo -e "${YELLOW}✓ Konfigürasyon dosyası oluşturuluyor...${NC}"

cat > "$SCRIPT_DIR/.env.php" <<EOF
<?php
return [
    'app_name' => '$HOTEL_NAME',
    'currency' => '$CURRENCY',
    'currency_symbol' => '$CURRENCY_SYMBOL',
    'check_in_time' => '$CHECK_IN_TIME',
    'check_out_time' => '$CHECK_OUT_TIME',
    'installed_at' => '$(date)',
    'installation_complete' => true
];
EOF

chmod 644 "$SCRIPT_DIR/.env.php"

echo -e "${GREEN}✓ Konfigürasyon dosyası oluşturuldu${NC}"
echo ""

# ============================================
# Final Steps
# ============================================

echo -e "${BLUE}════════════════════════════════════════${NC}"
echo -e "${GREEN}✓ Hotel Master Lite başarıyla kuruldu!${NC}"
echo -e "${BLUE}════════════════════════════════════════${NC}"
echo ""

echo "📋 Kurulum Özeti:"
echo "  • Otel Adı: $HOTEL_NAME"
echo "  • Para Birimi: $CURRENCY"
echo "  • Check-in: $CHECK_IN_TIME"
echo "  • Check-out: $CHECK_OUT_TIME"
echo "  • Yönetici E-posta: $ADMIN_EMAIL"
echo ""

echo -e "${YELLOW}🚀 Başlamak için:${NC}"
echo "  1. Web sunucusu başlatın:"
echo "     cd $SCRIPT_DIR"
echo "     php -S localhost:8000 -t public/"
echo ""
echo "  2. Tarayıcıda açın:"
echo "     http://localhost:8000"
echo ""
echo "  3. Giriş yapın:"
echo "     E-posta: $ADMIN_EMAIL"
echo "     Şifre: (girdiğiniz şifre)"
echo ""

echo -e "${BLUE}📖 Dokümantasyon:${NC}"
echo "  • README.md dosyasını okuyun"
echo "  • API belgeleri: docs/API.md"
echo ""

echo -e "${GREEN}✓ Kurulum tamamlandı!${NC}"
