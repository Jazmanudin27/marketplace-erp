# Quick Start Guide - Marketplace ERP

## 1. Setup Awal ✅ (Sudah Selesai)

Database sudah di-setup dengan:
- ✓ Companies table
- ✓ Users table  
- ✓ Roles table
- ✓ Company_User pivot table
- ✓ Marketplace_Accounts table
- ✓ Test data tersimpan

## 2. Akun Test

Gunakan akun berikut untuk testing:

| Email | Password | Role | Akses |
|-------|----------|------|-------|
| `admin@test.com` | `password` | Admin | Full access |
| `manager@test.com` | `password` | Manager | Manage accounts |
| `staff@test.com` | `password` | Staff | View only |

## 3. Mengakses Aplikasi

### Via URL
```
http://localhost:8000/login
http://localhost:8000/register
http://localhost:8000/dashboard
```

### Via Artisan
```bash
php artisan serve
```

Aplikasi akan berjalan di `http://localhost:8000`

## 4. Alur Penggunaan

### Sebagai Admin
1. Login dengan `admin@test.com` / `password`
2. Lihat dashboard dengan informasi company dan user
3. Akses "Marketplace Accounts" untuk connect platform
4. Pilih platform (Shopee, Tokopedia, TikTok, Lazada)
5. Redirect ke OAuth (akan ditampilkan pesan sukses setelah authorize)

### Sebagai Manager/Staff
1. Login dengan akun masing-masing
2. Lihat dashboard (read-only information)
3. Manager: Bisa view dan manage marketplace accounts
4. Staff: Hanya bisa view marketplace accounts

### Register Perusahaan Baru
1. Kunjungi `/register`
2. Isi detail perusahaan:
   - Nama perusahaan
   - Email perusahaan
   - Nomor telepon (opsional)
3. Isi data pengguna (yang akan menjadi admin):
   - Nama pengguna
   - Email pengguna
   - Password (min 8 karakter)
4. Submit form
5. Auto-login ke dashboard

## 5. Database Commands

```bash
# Reset & seed database
php artisan migrate:refresh --seed

# Hanya seed (jika table sudah ada)
php artisan db:seed

# Akses database shell
php artisan tinker

# Di dalam tinker
App\Models\User::all();
App\Models\Company::all();
App\Models\Role::all();
DB::table('company_user')->get();
```

## 6. Troubleshooting

### Error: "Class not found"
```bash
composer dump-autoload
php artisan cache:clear
```

### Error: "Migration table not found"
```bash
php artisan migrate:fresh --seed
```

### Error: Database connection
Pastikan `.env` file sudah dikonfigurasi:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=marketplace_erp
DB_USERNAME=root
DB_PASSWORD=
```

## 7. File Penting

| File | Fungsi |
|------|--------|
| `app/Models/Company.php` | Model perusahaan |
| `app/Models/User.php` | Model pengguna |
| `app/Models/Role.php` | Model roles |
| `app/Models/MarketplaceAccount.php` | Model akun marketplace |
| `app/Http/Controllers/Auth/RegisterController.php` | Registrasi |
| `app/Http/Controllers/Auth/LoginController.php` | Login |
| `app/Http/Controllers/MarketplaceConnectionController.php` | Koneksi marketplace |
| `routes/web.php` | Route definitions |
| `resources/views/auth/` | Auth views |
| `resources/views/dashboard.blade.php` | Dashboard |
| `resources/views/marketplace/` | Marketplace views |

## 8. Struktur Project

```
marketplace-erp/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   └── RegisterController.php
│   │   │   ├── MarketplaceConnectionController.php
│   │   │   └── WebhookController.php
│   │   ├── Middleware/
│   │   │   └── CompanyMiddleware.php
│   │   └── Kernel.php
│   ├── Models/
│   │   ├── Company.php
│   │   ├── User.php
│   │   ├── Role.php
│   │   └── MarketplaceAccount.php
│   ├── Policies/
│   │   └── MarketplaceAccountPolicy.php
│   └── Providers/
│       └── AppServiceProvider.php
├── database/
│   ├── migrations/
│   │   ├── 2026_05_25_034226_create_companies_table.php
│   │   ├── 2026_05_25_034230_create_roles_table.php
│   │   ├── 2026_05_25_034301_add_company_to_users_table.php
│   │   ├── 2026_05_25_034303_add_company_to_marketplace_accounts_table.php
│   │   └── 2026_05_25_034306_create_company_user_table.php
│   └── seeders/
│       ├── RoleSeeder.php
│       └── DatabaseSeeder.php
├── resources/
│   ├── views/
│   │   ├── auth/
│   │   │   ├── login.blade.php
│   │   │   └── register.blade.php
│   │   ├── marketplace/
│   │   │   ├── accounts.blade.php
│   │   │   └── account-detail.blade.php
│   │   └── dashboard.blade.php
│   └── css/
│       └── app.css
├── routes/
│   └── web.php
├── config/
│   └── app.php
└── AUTH_DOCUMENTATION.md
```

## 9. Next Steps

Setelah setup berhasil, hal yang perlu dikerjakan:

1. **Integrasi OAuth Real**
   - Daftar API key di Shopee, Tokopedia, TikTok, Lazada
   - Update `config/services.php` dengan credentials
   - Implementasikan token exchange

2. **Marketplace API Integration**
   - Setup service classes untuk setiap marketplace
   - Pull orders, products, dan data lainnya

3. **UI Improvement**
   - Gunakan component library (e.g., Bootstrap, Tailwind)
   - Buat responsive design

4. **Error Handling**
   - Add detailed error logging
   - Create error pages

5. **Testing**
   - Write unit tests
   - Write feature tests
   - Add integration tests

## 10. Helpful Commands

```bash
# Development
php artisan serve                    # Start dev server
php artisan tinker                   # Interactive shell

# Database
php artisan migrate                  # Run migrations
php artisan migrate:rollback         # Undo migrations
php artisan db:seed                  # Run seeders
php artisan migrate:fresh --seed     # Reset & seed

# Cache & Config
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Code Quality
composer install                     # Install dependencies
composer update                      # Update dependencies
```

---

**Last Updated:** 25 Mei 2026
