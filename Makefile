
test:
	#php -d "memory_limit=-1" vendor/bin/phpcs --report=full --report-file=./report.txt -p ./src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/RoadizCoreBundle/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/RoadizCompatBundle/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/RoadizRozierBundle/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/RoadizFontBundle/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/RoadizUserBundle/src
	php -d "memory_limit=-1" vendor/bin/phpstan analyse -c phpstan.neon
	php -d "memory_limit=-1" bin/console lint:twig ./lib

cache :
	docker-compose exec -u www-data app php bin/console cache:clear

migrate:
	docker-compose exec -u www-data app php bin/console doctrine:migrations:migrate
	docker-compose exec -u www-data app php bin/console themes:migrate ./src/Resources/config.yml
