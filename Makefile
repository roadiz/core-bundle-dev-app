phpstan:
	php -d "memory_limit=-1" vendor/bin/phpstan analyse -c phpstan.neon

test:
	vendor/bin/requirements-checker
	vendor/bin/monorepo-builder validate
	vendor/bin/atoum -d ./lib/Documents/tests
	vendor/bin/atoum -f ./lib/EntityGenerator/tests/units/*
	vendor/bin/phpunit -v
	php -d "memory_limit=-1" vendor/bin/phpcs -p
	make phpstan
	php -d "memory_limit=-1" bin/console lint:twig ./lib/Rozier/src/Resources/views
	php -d "memory_limit=-1" bin/console lint:twig ./lib/Documents/src/Resources/views
	php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizUserBundle/templates
	php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizRozierBundle/templates
	php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizFontBundle/templates
	php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizCoreBundle/templates
	php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizTwoFactorBundle/templates

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
