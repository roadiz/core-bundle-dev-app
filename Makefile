phpstan:
	php -d "memory_limit=-1" vendor/bin/phpstan analyse -c phpstan.neon

test:
	docker compose run --no-deps --rm --entrypoint= app vendor/bin/requirements-checker
	docker compose run --no-deps --rm --entrypoint= app vendor/bin/monorepo-builder validate
	make phpstan
	make rector_test
	docker compose run --no-deps --rm --entrypoint= -e "XDEBUG_MODE=coverage" app vendor/bin/phpunit -v
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" vendor/bin/php-cs-fixer fix --ansi -vvv
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/Documents/src/Resources/views
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizCoreBundle/templates
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizFontBundle/templates
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizRozierBundle/templates
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizTwoFactorBundle/templates
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizUserBundle/templates
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" bin/console lint:twig ./lib/Rozier/src/Resources/views

rector_test:
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" vendor/bin/rector process --dry-run

rector:
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" vendor/bin/rector process
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" vendor/bin/php-cs-fixer fix --ansi -vvv

phpunit:
	docker compose run --rm --entrypoint= -e "APP_ENV=test" app php php vendor/bin/phpunit -v

fix:
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" vendor/bin/php-cs-fixer fix --ansi -vvv

check:
	docker compose run --no-deps --rm --entrypoint= app php -d "memory_limit=-1" vendor/bin/php-cs-fixer check --ansi -vvv

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

migrate:
	docker compose exec app php bin/console doctrine:migrations:migrate
	docker compose exec app php bin/console app:migrate

update:
	docker compose exec app php bin/console doctrine:migrations:migrate -n
	docker compose exec app php bin/console app:install
