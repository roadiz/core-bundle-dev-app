phpstan:
	php -d "memory_limit=-1" vendor/bin/phpstan analyse -c phpstan.neon

test:
	vendor/bin/requirements-checker
	vendor/bin/monorepo-builder validate
	make phpstan
	XDEBUG_MODE=coverage vendor/bin/phpunit -v
	php -d "memory_limit=-1" vendor/bin/phpcs -p
	php -d "memory_limit=-1" bin/console lint:twig ./lib/Documents/src/Resources/views
	php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizCoreBundle/templates
	php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizFontBundle/templates
	php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizRozierBundle/templates
	php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizTwoFactorBundle/templates
	php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizUserBundle/templates
	php -d "memory_limit=-1" bin/console lint:twig ./lib/Rozier/src/Resources/views

phpunit:
	APP_ENV=test docker compose exec -u www-data app php vendor/bin/phpunit -v

fix:
	php -d "memory_limit=-1" vendor/bin/phpcbf -p

requirements:
	vendor/bin/requirements-checker

cache :
	docker compose exec -u www-data app php bin/console cache:clear
	docker compose exec -u www-data app bin/console cache:pool:clear cache.global_clearer
	# Force workers to restart
	docker compose exec -u www-data app php bin/console messenger:stop-workers

migrate:
	docker compose exec -u www-data app php bin/console doctrine:migrations:migrate
	docker compose exec -u www-data app php bin/console app:migrate

update:
	docker compose exec -u www-data app php bin/console doctrine:migrations:migrate -n
	docker compose exec -u www-data app php bin/console app:install
