phpstan:
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" vendor/bin/phpstan analyse -c phpstan.neon

check-architecture:
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" vendor/bin/deptrac analyse --config-file=deptrac.yaml --ansi --no-progress

audit:
	docker compose run --no-deps --rm --entrypoint= app composer audit --abandoned=report --format=plain

test:
	docker compose run --no-deps --rm --entrypoint= app vendor/bin/requirements-checker
	docker compose run --no-deps --rm --entrypoint= app vendor/bin/monorepo-builder validate
	docker compose run --no-deps --rm --entrypoint= app composer audit --abandoned=report --format=plain --locked
	make phpstan
	make rector_test
	make check-architecture
	make check
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/Documents/src/Resources/views
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizCoreBundle/templates
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizFontBundle/templates
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizRozierBundle/templates
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizTwoFactorBundle/templates
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizUserBundle/templates
	make phpunit

rector_test:
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" vendor/bin/rector process --dry-run

rector:
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" vendor/bin/rector process
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" vendor/bin/php-cs-fixer fix --ansi -vvv

check:
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" vendor/bin/php-cs-fixer check --ansi -vvv

phpunit:
	docker compose up -d --force-recreate app mariadb-test db-test
	sleep 3
	# Test with MariaDB 10.11
	docker compose exec -e "DATABASE_URL=mysql://db_user:db_password@mariadb-test/db_name?serverVersion=mariadb-10.11.9&charset=utf8mb4" -e "APP_ENV=test" -e "SYMFONY_DEPRECATIONS_HELPER=max[total]=999999" -e "XDEBUG_MODE=coverage" app vendor/bin/phpunit -v
	docker compose exec -e "DATABASE_URL=mysql://db_user:db_password@mariadb-test/db_name?serverVersion=mariadb-10.11.9&charset=utf8mb4" -e "APP_ENV=test" app bin/console -e test doctrine:database:drop --force
	# Test with MySQL 8.0
	docker compose exec -e "DATABASE_URL=mysql://db_user:db_password@db-test/db_name?serverVersion=8.0.42&charset=utf8mb4" -e "APP_ENV=test" -e "SYMFONY_DEPRECATIONS_HELPER=max[total]=999999" -e "XDEBUG_MODE=coverage" app vendor/bin/phpunit -v
	docker compose exec -e "DATABASE_URL=mysql://db_user:db_password@db-test/db_name?serverVersion=8.0.42&charset=utf8mb4" -e "APP_ENV=test" app bin/console -e test doctrine:database:drop --force
	docker compose stop mariadb-test db-test
	docker compose rm -f -v mariadb-test db-test

requirements:
	docker compose run --no-deps --rm --entrypoint= app vendor/bin/requirements-checker
	docker compose run --no-deps --rm --entrypoint= app vendor/bin/monorepo-builder validate

bash:
	docker compose exec app bash --login

cache :
	docker compose exec app php bin/console cache:clear
	docker compose exec app bin/console cache:pool:clear cache.global_clearer
	# Force workers to restart
	docker compose exec app php bin/console messenger:stop-workers
    # Restart app, worker and scheduler containers when using frankenphp
	docker compose up -d --force-recreate app worker scheduler

migrate:
	docker compose exec app php bin/console doctrine:migrations:migrate
	docker compose exec app php bin/console app:migrate

update:
	docker compose exec app php bin/console doctrine:migrations:migrate -n
	docker compose exec app php bin/console app:install
