# Dokumentasi Sistem Authentication - Marketplace ERP

## Gambaran Umum

Sistem authentication multi-company yang dirancang untuk mengelola marketplace dari berbagai platform (Shopee, Tokopedia, TikTok, Lazada) dalam satu aplikasi. Sistem ini mendukung:

- ✅ **Multi-Company:** Banyak perusahaan dapat menggunakan aplikasi yang sama
- ✅ **Multi-User per Company:** Setiap perusahaan dapat memiliki banyak pengguna dengan roles berbeda
- ✅ **Role-Based Access Control:** Admin, Manager, Staff dengan permission berbeda
- ✅ **Marketplace Integration:** Menghubungkan multiple akun dari berbagai platform

---

## Arsitektur Database

### 1. **Companies** Table
Menyimpan data perusahaan yang menggunakan aplikasi.

```sql
- id: Identifier unik
- name: Nama perusahaan (unique)
- slug: URL-friendly identifier (unique)
- email: Email perusahaan
- phone: Nomor telepon
- website: Website perusahaan
- logo: Path ke logo
- description: Deskripsi perusahaan
- status: active | inactive | suspended
- max_users: Maksimal pengguna yang diizinkan
- trial_ends_at: Tanggal trial berakhir
- timestamps: created_at, updated_at
```

### 2. **Users** Table
Menyimpan data pengguna (admin, manager, staff).

```sql
- id: Identifier unik
- name: Nama pengguna
- email: Email pengguna (unique)
- password: Password terenkripsi
- company_id: FK ke companies (perusahaan primary)
- status: active | inactive
- last_login_at: Timestamp login terakhir
- email_verified_at: Timestamp verifikasi email
- remember_token: Remember me token
- timestamps: created_at, updated_at
```

### 3. **Roles** Table
Menyimpan definisi roles dalam sistem.

```sql
- id: Identifier unik
- name: Nama role (unique) - admin, manager, staff
- display_name: Nama yang ditampilkan
- description: Deskripsi role
- timestamps: created_at, updated_at
```

**Default Roles:**
- `admin`: Full access to all features and user management
- `manager`: Can manage marketplace accounts and view reports
- `staff`: Can view marketplace accounts and orders

### 4. **Company_User** Table (Pivot)
Relasi many-to-many antara companies dan users dengan role assignment.

```sql
- id: Identifier unik
- company_id: FK ke companies (cascadeOnDelete)
- user_id: FK ke users (cascadeOnDelete)
- role_id: FK ke roles
- status: active | inactive
- unique(company_id, user_id): Satu user per company hanya 1 entry
- timestamps: created_at, updated_at
```

### 5. **Marketplace_Accounts** Table
Menyimpan akun marketplace yang terhubung.

```sql
- id: Identifier unik
- platform: shopee | tokopedia | tiktok | lazada
- shop_id: ID toko di platform
- shop_name: Nama toko
- access_token: Token akses dari OAuth (encrypted)
- refresh_token: Token refresh (encrypted)
- expired_at: Waktu token kadaluarsa
- company_id: FK ke companies
- timestamps: created_at, updated_at
```

---

## Models & Relationships

### 1. **Company Model**
```php
// Relationships
$company->users()                 // BelongsToMany
$company->marketplaceAccounts()   // HasMany
```

### 2. **User Model**
```php
// Relationships
$user->company()                  // BelongsTo (primary company)
$user->companies()                // BelongsToMany (all companies)
$user->roles()                    // BelongsToMany

// Methods
$user->hasRole('admin', $companyId)  // Check if user has role in company
```

### 3. **Role Model**
```php
// Relationships
$role->users()  // BelongsToMany
```

### 4. **MarketplaceAccount Model**
```php
// Relationships
$account->company()  // BelongsTo
```

---

## Controllers

### 1. **Auth/RegisterController**
Menangani registrasi perusahaan dan pengguna baru.

**Routes:**
- `GET /register` - Tampilkan form registrasi
- `POST /register` - Proses registrasi

**Proses:**
1. Validasi input perusahaan dan pengguna
2. Buat perusahaan baru
3. Buat pengguna dengan company_id = perusahaan
4. Assign role "admin" ke pengguna
5. Auto-login pengguna
6. Redirect ke dashboard

**Validation:**
```php
'company_name'     => 'required|string|max:255|unique:companies,name',
'company_email'    => 'required|email|unique:companies,email',
'company_phone'    => 'nullable|string|max:20',
'user_name'        => 'required|string|max:255',
'user_email'       => 'required|email|unique:users,email',
'password'         => 'required|string|min:8|confirmed',
```

### 2. **Auth/LoginController**
Menangani login pengguna.

**Routes:**
- `GET /login` - Tampilkan form login
- `POST /login` - Proses login
- `POST /logout` - Logout

**Proses Login:**
1. Validasi credentials (email + password)
2. Update `last_login_at`
3. Set session `company_id`
4. Redirect ke dashboard

### 3. **MarketplaceConnectionController**
Menangani koneksi akun marketplace.

**Routes:**
- `GET /marketplace/accounts` - List akun marketplace
- `GET /marketplace/accounts/{account}` - Detail akun
- `POST /marketplace/connect` - Inisiasi koneksi OAuth
- `GET /marketplace/callback` - OAuth callback
- `DELETE /marketplace/accounts/{account}` - Putus hubung akun

**Supported Platforms:**
- Shopee
- Tokopedia
- TikTok
- Lazada

---

## Middleware

### CompanyMiddleware
- Memastikan company_id tersimpan di session
- Validate company_id dengan companies yang dimiliki user
- Share `$currentCompany` ke semua views

**Aplikasi:**
- Diterapkan pada authenticated routes
- Bootstrap di `app/Http/Kernel.php`

---

## Authorization Policies

### MarketplaceAccountPolicy
Kontrol akses ke marketplace accounts berdasarkan company dan role.

**Permissions:**
| Method | Admin | Manager | Staff |
|--------|-------|---------|-------|
| viewAny | ✓ | ✓ | ✓ |
| view | ✓ | ✓ | ✓ |
| create | ✓ | ✓ | ✗ |
| update | ✓ | ✓ | ✗ |
| delete | ✓ | ✗ | ✗ |
| restore | ✓ | ✗ | ✗ |
| forceDelete | ✓ | ✗ | ✗ |

**Usage:**
```php
// Check authorization
$this->authorize('view', $account);
$this->authorize('delete', $account);

// In Blade templates
@can('view', $account)
    <a href="{{ route('marketplace.show', $account) }}">View</a>
@endcan
```

---

## Alur Autentikasi

### 1. Registrasi Baru (Perusahaan + Pengguna)
```
┌─────────────────────┐
│  Kunjungi /register │
└──────────┬──────────┘
           │
           ▼
┌──────────────────────────┐
│  Input Perusahaan & User │
└──────────┬───────────────┘
           │
           ▼
┌──────────────────────────┐
│  Validasi & Create Data  │
│  - Company created       │
│  - User created          │
│  - company_user record   │
│  - Role assigned (admin) │
└──────────┬───────────────┘
           │
           ▼
┌──────────────────────────┐
│  Auto-login Pengguna     │
│  Set session company_id  │
└──────────┬───────────────┘
           │
           ▼
┌──────────────────────────┐
│  Redirect ke Dashboard   │
└──────────────────────────┘
```

### 2. Login Pengguna Existing
```
┌─────────────────────┐
│  Kunjungi /login    │
└──────────┬──────────┘
           │
           ▼
┌──────────────────────────┐
│  Input Email & Password  │
└──────────┬───────────────┘
           │
           ▼
┌──────────────────────────┐
│  Validasi Credentials    │
└──────────┬───────────────┘
           │
           ▼ (Success)
┌──────────────────────────┐
│  Update last_login_at    │
│  Set session company_id  │
│  Generate session token  │
└──────────┬───────────────┘
           │
           ▼
┌──────────────────────────┐
│  Redirect ke Dashboard   │
└──────────────────────────┘
```

### 3. Koneksi Marketplace
```
┌─────────────────────────┐
│  Pilih Platform         │
│  (Shopee, Tokopedia dll)│
└──────────┬──────────────┘
           │
           ▼
┌──────────────────────────┐
│  Redirect ke OAuth URL   │
│  Platform tertentu       │
└──────────┬───────────────┘
           │ (User login & authorize)
           ▼
┌──────────────────────────┐
│  Callback dengan Code    │
└──────────┬───────────────┘
           │
           ▼
┌──────────────────────────┐
│  Exchange Code->Token    │
│  Save MarketplaceAccount │
└──────────┬───────────────┘
           │
           ▼
┌──────────────────────────┐
│  Redirect ke Accounts    │
│  Show Success Message    │
└──────────────────────────┘
```

---

## Test Data

Database sudah di-seed dengan data test:

### Company
```
Name: Test Company
Email: company@test.com
Phone: 081234567890
Status: active
```

### Users
| Email | Password | Role | Company |
|-------|----------|------|---------|
| admin@test.com | password | Admin | Test Company |
| manager@test.com | password | Manager | Test Company |
| staff@test.com | password | Staff | Test Company |

---

## Fitur Keamanan

### 1. **Password Hashing**
- Menggunakan bcrypt (Laravel default)
- Otomatis di-hash saat menyimpan

### 2. **CSRF Protection**
- Semua form dilindungi token CSRF

### 3. **Role-Based Access Control**
- Setiap action dilindungi policy
- Middleware memvalidasi company_id

### 4. **Token Encryption**
- Access & refresh token di-encrypt di database

### 5. **Session Management**
- Secure session dengan company context
- Auto-logout inactive users bisa ditambahkan

---

## Implementasi Selanjutnya

Fitur yang masih perlu dikembangkan:

### Priority 1 (Critical)
- [ ] Enkripsi access_token dan refresh_token
- [ ] Rate limiting pada login
- [ ] Email verification
- [ ] Password reset functionality
- [ ] Two-factor authentication (2FA)

### Priority 2 (Important)
- [ ] User management (invite, remove users)
- [ ] Audit logging
- [ ] API tokens untuk integrasi
- [ ] Permission-based access control
- [ ] Multi-role per user per company

### Priority 3 (Nice-to-have)
- [ ] Social login (Google, GitHub)
- [ ] Session timeout
- [ ] Activity tracking
- [ ] Dark mode
- [ ] Internationalization (i18n)

---

## Troubleshooting

### 1. User tidak bisa login
**Kemungkinan:**
- Password salah
- Akun inactive (`status = 'inactive'`)
- Email belum terdaftar

**Solusi:**
```php
// Check via tinker
$user = User::where('email', 'admin@test.com')->first();
echo $user->status;  // Harus 'active'

// Reset password jika perlu
$user->password = bcrypt('newpassword');
$user->save();
```

### 2. Authorization error pada marketplace accounts
**Kemungkinan:**
- User tidak memiliki role yang tepat
- Account milik company lain
- Policy tidak diterapkan di controller

**Solusi:**
```php
// Verify role
$user->hasRole('admin');
$user->hasRole('manager');

// Check company
$account->company_id === $user->company_id
```

### 3. Session company_id tidak tersimpan
**Solusi:**
- Pastikan CompanyMiddleware diterapkan
- Check session storage (default: file)
- Clear session: `php artisan cache:clear`

---

## API Endpoints (Future)

Planning untuk API authentication:

```
POST   /api/auth/register     - Register baru
POST   /api/auth/login        - Login
POST   /api/auth/logout       - Logout
POST   /api/auth/refresh      - Refresh token
GET    /api/marketplace/accounts
POST   /api/marketplace/connect/{platform}
DELETE /api/marketplace/accounts/{id}
```

---

## Contact & Support

Untuk pertanyaan atau issue, silakan hubungi developer.

**Last Updated:** 25 Mei 2026
