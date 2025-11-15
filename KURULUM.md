# Şikayetvar Platform - Kurulum Rehberi

## 🚀 Hızlı Başlangıç

### 1. Bağımlılıkları Yükle
```bash
cd /home/user/sikayet
composer install
```

### 2. Ortam Dosyasını Konfigüre Et
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Veritabanını Konfigüre Et
`.env` dosyasını aç ve database ayarlarını düzenle:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sikayet
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Veritabanını Oluştur ve Migrate Et
```bash
php artisan migrate --seed
```

### 5. Storage Link Oluştur
```bash
php artisan storage:link
```

### 6. Geliştirme Sunucusunu Başlat
```bash
php artisan serve
```

**URL**: http://localhost:8000

## 👤 Test Hesapları

| Rol | Email | Şifre | Panel |
|-----|-------|-------|-------|
| Super Admin | superadmin@sikayetvar.com | SuperAdmin123! | /admin |
| Moderator | moderator@sikayetvar.com | Moderator123! | /admin |
| Müşteri | customer@test.com | Customer123! | Frontend |
| Marka | brand@test.com | Brand123! | /brand |

## 🗂️ Proje Yapısı

```
app/
├── Models/                          # Eloquent Models (12 adet)
├── Http/Controllers/
│   ├── Admin/                       # 10 Admin Controller
│   ├── Brand/                       # 3 Brand Controller
│   └── Frontend/                    # 5 Frontend Controller
├── Http/Middleware/                 # Custom Middleware (3 adet)
└── Helpers/helpers.php              # Helper Fonksiyonları (10 adet)

database/
├── migrations/                      # 13 tablo migration'ı
└── seeders/                         # 5 seeder dosyası

resources/views/
├── layouts/                         # 3 base layout (app, admin, brand)
├── frontend/                        # Frontend pages
├── admin/                           # Admin pages (ready)
└── brand/                           # Brand pages (ready)

public/assets/
├── css/                             # 3 CSS dosyası
└── js/                              # 3 JavaScript dosyası

routes/web.php                       # Tüm route'lar tanımlı (90+ endpoint)
```

## 📊 Veritabanı Tabloları

1. **users** - Kullanıcılar (user_type: customer/brand/admin)
2. **categories** - Ağaç yapısında kategoriler
3. **brands** - Marka profilleri ve istatistikleri
4. **complaints** - Şikayetler (otomatik numaralandırma)
5. **complaint_attachments** - Şikayet ekleri
6. **complaint_comments** - Şikayet yorumları
7. **activity_logs** - Admin aktivite loglama
8. **blog_posts** - Blog yazıları
9. **pages** - Statik sayfalar
10. **settings** - Site ayarları (key-value)
11. **moderation_rules** - Moderasyon kuralları
12. **notifications** - Kullanıcı bildirimleri
13. **Spatie Permission** - Rol ve izinler

## 🎨 Frontend Pages

- `GET /` - Anasayfa (Hero, istatistikler, son şikayetler)
- `GET /sikayetler` - Şikayetler listesi (filtreli)
- `GET /sikayetler/{brand}/{number}` - Şikayet detayı
- `GET /sikayetler/yeni` - Yeni şikayet formu (Auth)
- `GET /markalar` - Markalar listesi
- `GET /markalar/{slug}` - Marka profili
- `GET /blog` - Blog listesi
- `GET /blog/{slug}` - Blog detayı
- `GET /profil` - Kullanıcı profili (Auth)
- `GET /ara` - Arama

## 🔐 Admin Panel (/admin)

- `GET /admin` - Dashboard (grafikler, istatistikler)
- `GET /admin/users` - Kullanıcı yönetimi
- `GET /admin/complaints` - Şikayet yönetimi (filter, bulk actions, export)
- `GET /admin/brands` - Marka yönetimi (onay, reddet, suspend)
- `GET /admin/categories` - Kategori yönetimi (drag&drop reorder)
- `GET /admin/blog` - Blog yönetimi
- `GET /admin/pages` - Sayfa yönetimi
- `GET /admin/settings` - Sistem ayarları
- `GET /admin/moderation/queue` - Moderasyon kuyruğu
- `GET /admin/analytics` - Analytics ve raporlar

## 🏪 Brand Panel (/brand)

- `GET /brand` - Dashboard (istatistikler)
- `GET /brand/complaints` - Şikayet listesi
- `POST /brand/complaints/{id}/respond` - Yanıt ver
- `POST /brand/complaints/{id}/solved` - Çözüldü işaretle
- `GET /brand/profile` - Profil ayarları

## 📦 Tanımlanmış Controllers

### Admin Controllers (10)
- DashboardController - Dashboard
- UserController - Kullanıcı CRUD + ban/unban
- ComplaintController - Şikayet CRUD + moderasyon
- BrandController - Marka CRUD + onay/reddet
- CategoryController - Kategori CRUD + reorder
- BlogController - Blog CRUD
- PageController - Sayfa CRUD
- SettingController - Ayarlar
- ModerationController - Moderasyon kuralları
- AnalyticsController - Analytics

### Brand Controllers (3)
- DashboardController - Brand dashboard
- ComplaintController - Şikayet yönetimi
- ProfileController - Profil yönetimi

### Frontend Controllers (5)
- HomeController - Anasayfa
- ComplaintController - Şikayet CRUD
- BrandController - Marka browser
- BlogController - Blog browser
- ProfileController - Profil yönetimi

## 🔧 Özel Middleware

1. **AdminMiddleware** - Admin erişimi kontrolü
2. **BrandMiddleware** - Marka erişimi kontrolü
3. **ActivityLoggerMiddleware** - Tüm admin işlemleri loglama

## 🆘 Helper Fonksiyonları

```php
setting($key, $default)           // Ayar getir
update_setting($key, $value)      // Ayar güncelle
format_number($number)            // Sayı format (TR: 1.000,50)
time_ago($datetime)               // "2 saat önce" formatt
truncate_text($text, $length)     // Metni kısalt
sanitize_html($html)              // HTML temizle
log_activity(...)                 // Aktivite logla
badge_status($status)             // Status badge HTML
badge_priority($priority)         // Öncelik badge HTML
complaint_number_format($id)      // C000001 format
```

## 📝 Seeders

Veritabanını otomatik verilerle doldur:

```bash
# Tüm seederleri çalıştır
php artisan db:seed

# Spesifik seeder
php artisan db:seed --class=UserSeeder

# Fresh migrate + seed
php artisan migrate:fresh --seed
```

**Oluşturulan Seeders:**
1. RolePermissionSeeder - Rol ve izinler
2. UserSeeder - 5 test kullanıcısı
3. CategorySeeder - 8 ana + 4 alt kategori
4. BrandSeeder - 4 demo marka
5. SettingSeeder - Platform ayarları

## 🎯 Temel Özellikler

✅ Full CRUD (Create, Read, Update, Delete)
✅ Role-Based Access Control (Spatie Permission)
✅ Şikayet moderasyon sistemi
✅ Marka istatistikleri ve index puanı
✅ Aktivite logging
✅ Excel export
✅ Responsive Bootstrap 5 tasarım
✅ DataTables, Select2, Chart.js integration
✅ Turkish language support
✅ File upload (Intervention Image)
✅ Email notifications (Laravel Mail)

## 📧 Email Konfigürasyonu

`.env` dosyasını düzenle:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@sikayetvar.com
MAIL_FROM_NAME="Şikayetvar"
```

## 🚀 Production Deployment

```bash
# Dependencies
composer install --optimize-autoloader --no-dev

# Environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --force
php artisan db:seed --force

# Caching
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Storage
php artisan storage:link
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Security
# Ensure .env is not publicly accessible
# Set APP_DEBUG=false
# Set APP_ENV=production
```

## 🐛 Troubleshooting

### "Class not found" hatası
```bash
composer dump-autoload
```

### Migration hatası
```bash
php artisan migrate:refresh --seed
```

### Storage/uploads 404
```bash
php artisan storage:link
```

### Permission denied
```bash
chmod -R 755 storage bootstrap/cache
```

## 📞 Destek

Sorularınız için: support@sikayetvar.com

