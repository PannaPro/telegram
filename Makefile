up:
	docker compose up --build
build:
	docker compose build
down:
	docker compose down
cli:
	docker compose exec app bash
cc:
	docker compose exec app bin/console ca:cl
test:
	docker compose exec php bin/phpunit
watch:
	docker compose exec telegram-app bin/console telegram:get-updates --watch

prod-up:
	docker compose -f compose.yaml -f compose.prod.yaml down
	docker compose -f compose.yaml -f compose.prod.yaml build
	docker compose -f compose.yaml -f compose.prod.yaml up -d
prod-down:
	docker compose -f compose.yaml -f compose.prod.yaml down
