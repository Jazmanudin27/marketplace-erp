# 📁 Marketplace ERP - Struktur Project

## 📊 Organisasi Folder

```
marketplace-erp/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                # Authentication Controllers
│   │   │   │   ├── LoginController.php
│   │   │   │   └── RegisterController.php
│   │   │   ├── MarketplaceConnectionController.php  # Marketplace OAuth/Connection
│   │   │   ├── WebhookController.php              # Marketplace Webhooks
│   │   │   └── DashboardController.php             # Dashboard UI
│   │   ├── Middleware/              # Custom Middleware
│   │   │   └── CompanyMiddleware.php
│   │   └── Requests/                # Form Requests (Validation)
│   ├── Models/
│   │   ├── User.php
│   │   ├── Company.php
│   │   ├── Role.php
│   │   ├── MarketplaceAccount.php   # Connected marketplace accounts
│   │   ├── Product.php              # Products from all marketplaces
│   │   ├── Order.php                # Orders from all marketplaces
│   │   ├── OrderItem.php            # Order line items
│   │   └── Customer.php             # Customers from all marketplaces
│   ├── Services/                    # Business Logic
│   │   ├── Marketplace/
│   │   │   ├── MarketplaceManager.php       # Driver pattern manager
│   │   │   ├── Contracts/
│   │   │   │   └── MarketplaceInterface.php  # Interface for all marketplaces
│   │   │   ├── Shopee/
│   │   │   │   └── ShopeeService.php        # Shopee API integration
│   │   │   ├── TikTok/
│   │   │   │   └── TiktokService.php        # TikTok API integration
│   │   │   ├── Tokopedia/
│   │   │   │   └── TokopediaService.php    # Tokopedia API integration
│   │   │   ├── Connectors/                  # Additional marketplace connectors
│   │   │   └── Integrations/                # Integration helpers
│   ├── DTOs/                        # Data Transfer Objects
│   │   ├── ProductDTO.php           # Normalize product data
│   │   └── OrderDTO.php             # Normalize order data
│   ├── Jobs/                        # Queue Jobs
│   │   ├── Sync/
│   │   │   ├── SyncProductsJob.php          # Sync products from marketplaces
│   │   │   └── SyncOrdersJob.php            # Sync orders from marketplaces
│   │   ├── Marketplace/
│   │   │   └── ProcessMarketplaceOrderJob.php
│   │   └── Order/
│   │       └── SyncOrderStatusJob.php
│   ├── Events/                      # Event Classes
│   ├── Listeners/                   # Event Listeners
│   ├── Exceptions/                  # Custom Exceptions
│   ├── Traits/                      # Reusable Traits
│   └── Providers/                   # Service Providers
├── database/
│   ├── migrations/                  # Database Migrations
│   │   ├── create_users_table.php
│   │   ├── create_companies_table.php
│   │   ├── create_roles_table.php
│   │   ├── create_marketplace_accounts_table.php
│   │   ├── create_products_table.php
│   │   ├── create_orders_table.php
│   │   ├── create_order_items_table.php
│   │   └── create_customers_table.php
│   ├── seeders/                     # Database Seeders
│   └── factories/                   # Model Factories
├── routes/
│   ├── web.php                      # Web Routes (Dashboard, Auth, Marketplace Connection)
│   ├── console.php                  # Artisan Commands
│   └── api.php                      # API Routes (if needed for internal use)
├── resources/
│   ├── views/                       # Blade Templates
│   │   ├── auth/
│   │   ├── marketplace/
│   │   └── dashboard.blade.php
│   ├── js/
│   └── css/
├── storage/
│   ├── uploads/
│   │   ├── products/               # Product Images
│   │   └── documents/              # Documents
│   ├── logs/
│   └── app/
├── docker/
│   ├── Dockerfile                   # PHP-FPM Docker Image
│   └── nginx.conf                   # Nginx Configuration
├── tests/                           # Unit & Feature Tests
│   ├── Unit/
│   ├── Feature/
│   └── Integration/
├── docker-compose.yml               # Docker Compose Configuration
├── .dockerignore                    # Docker Build Ignore
└── composer.json                    # PHP Dependencies
```

## 🚀 Teknologi Stack

- **Framework**: Laravel 12
- **PHP**: 8.2+
- **Database**: MySQL 8.0
- **Cache/Queue**: Redis 7
- **Web Server**: Nginx
- **Containerization**: Docker & Docker Compose
- **Job Queue**: Laravel Horizon + Redis
- **Purpose**: Consume data from marketplace APIs (Shopee, TikTok, Tokopedia, etc.)

## 📡 Marketplace API Integration

### Supported Marketplaces

- **Shopee**: Product sync, Order sync, Stock update, Token refresh
- **TikTok**: Product sync, Order sync, Stock update, Token refresh
- **Tokopedia**: Product sync, Order sync, Stock update, Token refresh

### Data Flow

```
Marketplace APIs (Shopee, TikTok, Tokopedia)
    ↓
Marketplace Services (ShopeeService, TikTokService, TokopediaService)
    ↓
DTOs (ProductDTO, OrderDTO) - Normalize data
    ↓
Sync Jobs (SyncProductsJob, SyncOrdersJob) - Background processing
    ↓
Database (Products, Orders, OrderItems, Customers)
    ↓
Dashboard UI - View and manage data
```

### Web Routes Structure

```
/                           # Home page
/login                      # Login form
/register                   # Register form
/logout                     # Logout
/dashboard                  # Dashboard (requires auth + company)
/marketplace/accounts       # List connected marketplace accounts
/marketplace/accounts/{id}  # View marketplace account details
/marketplace/connect        # Connect new marketplace account
/marketplace/callback       # OAuth callback from marketplace
/marketplace/accounts/{id}  # Disconnect marketplace account (DELETE)
/webhook/shopee             # Shopee webhook endpoint
/webhook/tiktok             # TikTok webhook endpoint
/webhook/tokopedia          # Tokopedia webhook endpoint
```

## 🐳 Docker Commands

### Setup Awal
```bash
# Build images
docker-compose build

# Start services
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate

# Run seeders
docker-compose exec app php artisan db:seed
```

### Development
```bash
# View logs
docker-compose logs -f app

# SSH ke container
docker-compose exec app bash

# Run Artisan command
docker-compose exec app php artisan command:name

# Run tests
docker-compose exec app php artisan test

# Sync products from marketplace
docker-compose exec app php artisan sync:products {account_id}

# Sync orders from marketplace
docker-compose exec app php artisan sync:orders {account_id}
```

### Cleanup
```bash
# Stop containers
docker-compose down

# Remove volumes
docker-compose down -v
```

## 📝 Marketplace Configuration

### Environment Variables (.env)

```bash
# Shopee Configuration
SHOPEE_HOST=https://partner.shopeemobile.com
SHOPEE_PARTNER_ID=your_partner_id
SHOPEE_PARTNER_KEY=your_partner_key

# TikTok Configuration
TIKTOK_HOST=https://open.tiktokapis.com
TIKTOK_APP_ID=your_app_id
TIKTOK_APP_SECRET=your_app_secret

# Tokopedia Configuration
TOKOPEDIA_HOST=https://fs.tokopedia.net
TOKOPEDIA_CLIENT_ID=your_client_id
TOKOPEDIA_CLIENT_SECRET=your_client_secret
```

## ✅ Checklist Setup

- [ ] Update `.env` dengan konfigurasi marketplace APIs
- [ ] Run migrations: `php artisan migrate`
- [ ] Connect marketplace accounts via dashboard
- [ ] Configure cron jobs for automatic sync
- [ ] Setup Laravel Horizon for queue monitoring
- [ ] Test webhook endpoints
- [ ] Configure marketplace developer apps
- [ ] Setup proper error handling and logging

## 🔄 Workflow Development

1. **Add New Marketplace** → Create service in `app/Services/Marketplace/{MarketplaceName}/`
2. **Implement Interface** → Implement MarketplaceInterface methods
3. **Create DTOs** → Create DTOs for data normalization
4. **Add to Manager** → Register in MarketplaceManager
5. **Test Integration** → Test API connection and data sync
6. **Create Jobs** → Create sync jobs if needed
7. **Update Documentation** → Update PROJECT_STRUCTURE.md
