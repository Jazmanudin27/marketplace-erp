# Marketplace ERP - Setup dan Quick Start

## 🚀 Quick Start dengan Docker

### 1. Clone dan Setup
```bash
# Jika belum ada .env
cp .env.example .env

# Generate app key
docker-compose run --rm app php artisan key:generate
```

### 2. Build dan Run Containers
```bash
docker-compose up -d
```

### 3. Setup Database
```bash
# Run migrations
docker-compose exec app php artisan migrate

# Seed database (opsional)
docker-compose exec app php artisan db:seed
```

### 4. Install Dependencies
```bash
docker-compose exec app composer install
docker-compose exec app npm install && npm run build
```

## 📊 Akses Services

- **Application**: http://localhost
- **API**: http://localhost/api/v1
- **MySQL**: localhost:3306
- **Redis**: localhost:6379
- **Laravel Horizon**: http://localhost/horizon (Queue monitoring)

## 🔧 Useful Docker Commands

```bash
# View logs
docker-compose logs -f app

# SSH ke app container
docker-compose exec app bash

# SSH ke database
docker-compose exec mysql bash
mysql -u marketplace -p

# Run artisan command
docker-compose exec app php artisan tinker

# Run tests
docker-compose exec app php artisan test

# Queue worker
docker-compose exec app php artisan queue:work
```

## 📁 Directory Permissions

```bash
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

## 🧪 Testing

```bash
# Run semua tests
docker-compose exec app php artisan test

# Run specific test
docker-compose exec app php artisan test tests/Feature/Api/UserTest.php

# Run dengan coverage
docker-compose exec app php artisan test --coverage
```

## 📋 Environment Variables (.env)

Key variables untuk development:

```env
APP_NAME="Marketplace ERP"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=marketplace_erp
DB_USERNAME=marketplace
DB_PASSWORD=marketplace123

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## 🛑 Troubleshooting

### Containers tidak start
```bash
# Check logs
docker-compose logs

# Rebuild containers
docker-compose down
docker-compose up --build
```

### Database connection error
```bash
# Restart database
docker-compose restart mysql

# Check MySQL logs
docker-compose logs mysql
```

### Permission denied errors
```bash
docker-compose exec app chmod -R 777 storage bootstrap/cache
```

### Redis connection error
```bash
# Restart Redis
docker-compose restart redis

# Test connection
docker-compose exec redis redis-cli ping
```
