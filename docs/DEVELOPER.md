# Geliştirici Rehberi - Hotel Master Lite

Bu belge, Hotel Master Lite projesine katkı yapmak veya kodu özelleştirmek isteyen geliştiriciler için rehberdir.

## Kurulum (Geliştirme Ortamı)

### Gereksinimler
- PHP 8.2+
- SQLite3
- Nginx veya Apache
- Git

### Lokal Kurulum

```bash
# 1. Repository'yi klonla
git clone https://github.com/yourusername/lumina.git
cd lumina

# 2. Composer paketlerini yükle (opsiyonel - bunu kullanmıyoruz)
# Bu proje sıfır bağımlılıkla tasarlandı

# 3. Veritabanını başlat
php -r "require 'database/init.php';"

# 4. Dizin izinlerini ayarla
chmod -R 775 storage/
chmod 644 database/hotel.db

# 5. Geliştirme sunucusunu başlat
php -S localhost:8000 -t public/
```

Tarayıcıda açın: `http://localhost:8000`

---

## Proje Yapısı

```
lumina/
├── config/                 # Yapılandırma dosyaları
│   ├── constants.php      # Sabitler ve ayarlar
│   ├── config.php         # Temel konfigürasyon
│   └── roles.php          # RBAC izinleri
├── database/
│   ├── schema.sql         # Veritabanı şeması
│   └── init.php           # Veritabanı başlatma
├── src/
│   ├── core/              # Çekirdek sınıflar
│   │   ├── Database.php
│   │   ├── Auth.php
│   │   ├── Router.php
│   │   ├── Logger.php
│   │   └── Response.php
│   ├── controllers/       # Kontrolcüler (API endpoints)
│   ├── models/            # Veri modelleri
│   ├── middleware/        # Ara katman yazılımı
│   ├── utils/             # Yardımcı sınıflar
│   └── helpers/           # Yardımcı fonksiyonlar
├── public/                # Web kökü
│   ├── index.php          # API router
│   ├── *.html             # UI sayfaları
│   ├── css/               # Stil sayfaları
│   ├── js/                # İstemci tarafı JavaScript
│   └── images/            # Resimler ve ikonlar
├── storage/               # Dinamik dosyalar
│   ├── logs/              # Uygulama günlükleri
│   ├── exports/           # Dışa aktarılan dosyalar
│   ├── backups/           # Veritabanı yedekleri
│   └── uploads/           # Kullanıcı yüklended dosyalar
├── docs/                  # Belgeler
├── tests/                 # Birim testleri
├── docker-compose.yml     # Docker konfigürasyonu
├── Dockerfile             # Docker görüntü
└── README.md              # Proje README'si
```

---

## Temel Mimarı

### 1. Veritabanı Katmanı (`src/core/Database.php`)

```php
// Singleton kullanarak veritabanı bağlantısı
$db = Database::getInstance();

// Hazırlanan ifadeleri kullan (SQL injection koruması)
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
```

**Özellikler:**
- PDO tabanlı
- SQLite3 desteği
- Otomatik bağlantı
- Hazırlanan ifadeler enforsu
- Hata yönetimi

### 2. Kimlik Doğrulama (`src/core/Auth.php`)

```php
// Kullanıcı girişi
Auth::login($email, $password);

// Oturumu kontrol et
if (!Auth::isAuthenticated()) {
    Response::unauthorized('Oturum açın');
}

// İzin kontrol et
if (!Auth::hasPermission('users.create')) {
    Response::forbidden('Bu işleme izniniz yok');
}
```

**Özellikler:**
- Bcrypt şifrelemesi
- Seans tabanlı
- CSRF tokenler
- Role tabanlı erişim kontrolü

### 3. Router (`src/core/Router.php`)

```php
// API router'ını başlat
$router = new Router();

// Route'u kaydet
$router->get('/users', 'UserController@index');
$router->post('/users', 'UserController@create');
$router->put('/users/{id}', 'UserController@update');
$router->delete('/users/{id}', 'UserController@delete');

// İsteği yönlendir
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
```

### 4. Response Formatı (`src/core/Response.php`)

```php
// Başarılı yanıt
Response::success('İşlem başarılı', ['user' => $user], 201);

// Hata yanıtı
Response::error('E-posta gerekli', 422);

// Sayfalı yanıt
Response::paginated($items, $page, $pageSize, $total);
```

---

## Yeni Endpoint Ekleme

### Adım 1: Kontrolcü Oluştur

`src/controllers/ProductController.php`:
```php
<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Models\Product;

class ProductController {
    public function index() {
        Auth::requirePermission('products.view');
        
        $products = Product::all();
        Response::success('Ürünler', $products);
    }
    
    public function store() {
        Auth::requirePermission('products.create');
        
        $name = $_POST['name'] ?? null;
        
        if (!$name) {
            Response::error('Ürün adı gerekli', 422);
        }
        
        $product = Product::create(['name' => $name]);
        Response::success('Ürün oluşturuldu', $product, 201);
    }
}
```

### Adım 2: Model Oluştur

`src/models/Product.php`:
```php
<?php
namespace App\Models;

use App\Core\Database;

class Product {
    protected static $table = 'products';
    
    public static function all() {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM " . self::$table);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public static function create($data) {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "INSERT INTO " . self::$table . " (name) VALUES (?)"
        );
        $stmt->execute([$data['name']]);
        return ['id' => $db->lastInsertId(), ...$data];
    }
}
```

### Adım 3: Router'a Route Ekle

`public/index.php`:
```php
// Route'u ekle
$router->get('/products', 'ProductController@index');
$router->post('/products', 'ProductController@store');
```

### Adım 4: RBAC İzni Ekle

`config/roles.php`:
```php
'products.view' => ['admin', 'receptionist'],
'products.create' => ['admin'],
```

---

## Günlüğe Yazmak

```php
use App\Core\Logger;

// Farklı log seviyeleri
Logger::info('Kullanıcı giriş yaptı', ['user_id' => 123]);
Logger::error('Veritabanı bağlantısı başarısız', $error);
Logger::debug('Hata ayıklama mesajı', $data);

// Denetim günlüğü
Logger::audit('user_updated', 'User', 123, 
    ['old_name' => 'Eski Ad', 'new_name' => 'Yeni Ad']
);
```

---

## Doğrulama

```php
use App\Utils\Validator;

$validator = new Validator();

// Doğrulamalar
$validator->required('email', $_POST['email']);
$validator->email('email', $_POST['email']);
$validator->phone('phone', $_POST['phone']);  // TR formatı
$validator->unique('email', $_POST['email'], 'users');

// Sonuç - hata varsa false döner
if (!$validator->validate()) {
    Response::error('Doğrulama hatası', 422, [
        'errors' => $validator->getErrors()
    ]);
}
```

---

## Veritabanı Migrasyonları

Yeni tablo eklemek için `database/schema.sql`'i düzenle:

```sql
CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_products_name ON products(name);
```

Sonra veritabanını yenile:
```bash
php -r "
\$db = new \PDO('sqlite:database/hotel.db');
\$db->exec(file_get_contents('database/schema.sql'));
"
```

---

## Frontend Geliştirme

### HTML Sayfaları Oluştur

`public/products.html`:
```html
<!DOCTYPE html>
<html>
<head>
    <title>Ürünler</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <h1>Ürün Yönetimi</h1>
    <table id="productsTable">
        <thead>
            <tr>
                <th>Adı</th>
                <th>Fiyat</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    
    <script src="/js/app.js"></script>
    <script>
        // API çağrısı
        fetch('/api/products')
            .then(r => r.json())
            .then(data => {
                const table = document.getElementById('productsTable');
                data.data.forEach(product => {
                    const row = table.insertRow();
                    row.innerHTML = `
                        <td>${product.name}</td>
                        <td>${app.formatCurrency(product.price)}</td>
                    `;
                });
            });
    </script>
</body>
</html>
```

### CSS Stil Ekleme

`public/css/style.css`'e yardımcı sınıflar ekle:

```css
/* Ürün kartları */
.product-card {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.product-card h3 {
    margin: 0 0 0.5rem 0;
    color: #C4886C;
}
```

---

## Testler Yazma

`tests/unit/ProductTest.php`:
```php
<?php
class ProductTest {
    public function test_product_creation() {
        $product = Product::create(['name' => 'Test Ürün']);
        assert($product['name'] === 'Test Ürün');
        assert($product['id'] > 0);
    }
}
```

Testleri çalıştır:
```bash
php vendor/bin/phpunit tests/
```

---

## Performans İyileştirmeleri

### 1. Veritabanı Sorgularını Optimize Et

```php
// Kötü - n+1 problemi
$users = User::all();
foreach ($users as $user) {
    $reservations = Reservation::where('user_id', $user['id'])->get();
}

// İyi - JOIN kullan
$users = $db->query(
    "SELECT u.*, COUNT(r.id) as reservation_count 
     FROM users u 
     LEFT JOIN reservations r ON u.id = r.user_id 
     GROUP BY u.id"
)->fetchAll();
```

### 2. Cache Kullan

```php
$cacheKey = 'occupancy_rate_' . date('Y-m-d');
$occupancy = apcu_fetch($cacheKey);

if ($occupancy === false) {
    $occupancy = calculateOccupancy();
    apcu_store($cacheKey, $occupancy, 3600);
}
```

### 3. Dizinler Ekle

```sql
CREATE INDEX idx_reservations_dates ON reservations(check_in, check_out);
CREATE INDEX idx_customers_email ON customers(email);
```

---

## Güvenlik Best Practices

1. **Her zaman hazırlanan ifadeleri kullan**
   ```php
   $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
   $stmt->execute([$email]); // NE de bu: $db->query("... WHERE email = '$email'");
   ```

2. **CSRF koruması**
   ```php
   Auth::generateCSRFToken();
   // Formda: <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
   ```

3. **İstemci tarafı girdisini temizle**
   ```php
   $name = htmlspecialchars($_POST['name']);
   ```

4. **Hassas verileri günlüğe yazma**
   ```php
   // Kötü
   Logger::info('Kullanıcı verisi', ['password' => $password]);
   
   // İyi
   Logger::info('Kullanıcı giriş', ['user_id' => $user['id']]);
   ```

---

## Sorun Giderme

### "Veritabanı kilitleme hatası"
```bash
# WAL dosyasını sıfırla
rm database/hotel.db-wal database/hotel.db-shm
```

### "404 route bulunamadı"
- Route'un doğru şekilde kaydedildiğini kontrol et
- Request method'u eşleşiyor mu? (GET vs POST)

### "Değişiklikler kaydedilmiyor"
- Storage/ dizisi yazılabilir mi?
- Veritabanı dosyası kilitli mi?

---

**Son Güncelleme**: Şubat 2026

Sorular mı var? GitHub Issues'ta açın!
