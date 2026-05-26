.PHONY: help build up down logs shell artisan npm test migrate seed tinker clean

help:
	@echo "Marketplace ERP - Makefile Commands"
	@echo "===================================="
	@echo ""
	@echo "Docker Commands:"
	@echo "  make build          - Build Docker images"
	@echo "  make up             - Start Docker containers"
	@echo "  make down           - Stop Docker containers"
	@echo "  make logs           - View container logs (app)"
	@echo "  make clean          - Remove containers and volumes"
	@echo ""
	@echo "Development Commands:"
	@echo "  make shell          - SSH into app container"
	@echo "  make artisan        - Run artisan command (usage: make artisan CMD='migrate')"
	@echo "  make npm            - Run npm command (usage: make npm CMD='run dev')"
	@echo ""
	@echo "Database Commands:"
	@echo "  make migrate        - Run migrations"
	@echo "  make seed           - Run seeders"
	@echo "  make tinker         - Open tinker REPL"
	@echo ""
	@echo "Testing Commands:"
	@echo "  make test           - Run tests"
	@echo "  make test-coverage  - Run tests with coverage report"

build:
	docker-compose build

up:
	docker-compose up -d
	@echo "✓ Containers started"
	@echo "App: http://localhost"
	@echo "API: http://localhost/api/v1"

down:
	docker-compose down

logs:
	docker-compose logs -f app

logs-mysql:
	docker-compose logs -f mysql

logs-redis:
	docker-compose logs -f redis

shell:
	docker-compose exec app bash

artisan:
	docker-compose exec app php artisan $(CMD)

npm:
	docker-compose exec app npm $(CMD)

migrate:
	docker-compose exec app php artisan migrate

migrate-fresh:
	docker-compose exec app php artisan migrate:fresh

seed:
	docker-compose exec app php artisan db:seed

tinker:
	docker-compose exec app php artisan tinker

test:
	docker-compose exec app php artisan test

test-coverage:
	docker-compose exec app php artisan test --coverage

install: build up
	docker-compose exec app composer install
	docker-compose exec app npm install
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate
	@echo "✓ Installation complete!"

clean:
	docker-compose down -v
	@echo "✓ Containers and volumes removed"

queue-listen:
	docker-compose exec app php artisan queue:listen

horizon:
	@echo "Visit http://localhost/horizon"
	docker-compose exec app php artisan horizon

pint:
	docker-compose exec app php artisan pint

pint-check:
	docker-compose exec app php artisan pint --test
