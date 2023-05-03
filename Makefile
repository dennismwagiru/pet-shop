install:
	cp .env.example .env
	@make build
	@make up
	docker compose exec app composer install
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan storage:link
	docker compose exec app chmod -R 777 storage bootstrap/cache
	docker compose exec app php artisan db:wait
	@make fresh
up:
	docker compose up -d
build:
	docker compose build
stop:
	docker compose stop
migrate:
	docker compose exec app php artisan migrate
fresh:
	docker compose exec app php artisan migrate:fresh --seed
seed:
	docker compose exec app php artisan db:seed
test:
	docker compose exec app php artisan migrate --env=testing
	docker compose exec app php artisan test
