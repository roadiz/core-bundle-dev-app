phpstan:
	docker compose run --no-deps -u www-data --rm --entrypoint= app php -d "memory_limit=-1" vendor/bin/phpstan analyse -c phpstan.neon

test:
	docker compose run --no-deps -u www-data --rm --entrypoint= app php -d "memory_limit=-1" vendor/bin/requirements-checker
	docker compose run --no-deps -u www-data --rm --entrypoint= app php -d "memory_limit=-1" vendor/bin/monorepo-builder validate
	make phpstan
	XDEBUG_MODE=coverage vendor/bin/phpunit -v
	docker compose run --no-deps -u www-data --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/Rozier/src/Resources/views
	docker compose run --no-deps -u www-data --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/Documents/src/Resources/views
	docker compose run --no-deps -u www-data --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizUserBundle/templates
	docker compose run --no-deps -u www-data --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizRozierBundle/templates
	docker compose run --no-deps -u www-data --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizFontBundle/templates
	docker compose run --no-deps -u www-data --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizCoreBundle/templates
	docker compose run --no-deps -u www-data --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizTwoFactorBundle/templates

fix:
	docker compose run --no-deps -u www-data --rm --entrypoint= app php -d "memory_limit=-1" vendor/bin/phpcbf -p

requirements:
	vendor/bin/requirements-checker

cache :
	docker compose run --no-deps -u www-data --rm --entrypoint= app php -d "memory_limit=-1" bin/console cache:clear
	docker compose run --no-deps -u www-data --rm --entrypoint= app php -d "memory_limit=-1" bin/console cache:pool:clear cache.global_clearer
	# Force workers to restart
	docker compose run --no-deps -u www-data --rm --entrypoint= app php -d "memory_limit=-1" bin/console messenger:stop-workers

migrate:
	docker compose exec -u www-data app php bin/console doctrine:migrations:migrate
	docker compose exec -u www-data app php bin/console app:migrate

update:
	docker compose exec -u www-data app php bin/console doctrine:migrations:migrate -n
	docker compose exec -u www-data app php bin/console app:install
