
test:
	vendor/bin/atoum -d ./lib/Documents/tests
	#php -d "memory_limit=-1" vendor/bin/phpcs --report=full --report-file=./report.txt -p ./src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/RoadizCoreBundle/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/RoadizCompatBundle/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/RoadizRozierBundle/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/RoadizFontBundle/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/RoadizUserBundle/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/Rozier/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/Models/src
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/Documents/src
	php -d "memory_limit=-1" vendor/bin/phpstan analyse -c phpstan.neon
	php -d "memory_limit=-1" bin/console lint:twig ./lib

cache :
	docker-compose exec -u www-data app php bin/console cache:clear
	# Force workers to restart
	docker-compose exec -u www-data app php bin/console messenger:stop-workers

migrate:
	docker-compose exec -u www-data app php bin/console doctrine:migrations:migrate
	docker-compose exec -u www-data app php bin/console themes:migrate ./src/Resources/config.yml

pull:
	cd lib/RoadizCompatBundle && git pull
	cd lib/RoadizCoreBundle && git pull
	cd lib/RoadizFontBundle && git pull
	cd lib/RoadizRozierBundle && git pull
	cd lib/RoadizUserBundle && git pull
	cd lib/Rozier && git pull
	cd lib/Models && git pull
	cd lib/Documents && git pull
