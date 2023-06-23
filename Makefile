phpstan:
	php -d "memory_limit=-1" vendor/bin/phpstan analyse -c phpstan.neon

test:
	vendor/bin/requirements-checker
	vendor/bin/monorepo-builder validate
	vendor/bin/atoum -d ./lib/Documents/tests
	vendor/bin/atoum -f ./lib/EntityGenerator/tests/units/*
	vendor/bin/phpunit -v  lib/Models/tests
	#php -d "memory_limit=-1" vendor/bin/phpcs --report=full --report-file=./report.txt -p ./src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/DocGenerator/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/Documents/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/DtsGenerator/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/EntityGenerator/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/Jwt/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/Markdown/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/Models/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/OpenId/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/Random/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/RoadizCompatBundle/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/RoadizCoreBundle/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/RoadizFontBundle/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/RoadizRozierBundle/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/RoadizUserBundle/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/RoadizTwoFactorBundle/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/Rozier/src
	make phpstan
	php -d "memory_limit=-1" bin/console lint:twig ./lib/Rozier/src/Resources/views
	php -d "memory_limit=-1" bin/console lint:twig ./lib/Documents/src/Resources/views
	php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizUserBundle/templates
	php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizRozierBundle/templates
	php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizFontBundle/templates
	php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizCoreBundle/templates
	php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizTwoFactorBundle/templates

requirements:
	vendor/bin/requirements-checker

cache :
	docker-compose exec -u www-data app php bin/console cache:clear
	docker-compose exec -u www-data app bin/console cache:pool:clear cache.global_clearer
	# Force workers to restart
	docker-compose exec -u www-data app php bin/console messenger:stop-workers

migrate:
	docker-compose exec -u www-data app php bin/console doctrine:migrations:migrate
	docker-compose exec -u www-data app php bin/console themes:migrate ./src/Resources/config.yml
