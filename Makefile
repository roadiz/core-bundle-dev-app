
test:
	vendor/bin/monorepo-builder validate
	vendor/bin/atoum -d ./lib/Documents/tests
	vendor/bin/atoum -f ./lib/EntityGenerator/tests/units/*
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
	php -d "memory_limit=-1" vendor/bin/phpcbf -p ./lib/Rozier/src
	php -d "memory_limit=-1" vendor/bin/phpstan analyse -c phpstan.neon
	php -d "memory_limit=-1" bin/console lint:twig ./lib/Rozier/src/Resources/views
	php -d "memory_limit=-1" bin/console lint:twig ./lib/Documents/src/Resources/views
	php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizUserBundle/templates
	php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizRozierBundle/templates
	php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizFontBundle/templates
	php -d "memory_limit=-1" bin/console lint:twig ./lib/RoadizCoreBundle/templates


cache :
	docker-compose exec -u www-data app php bin/console cache:clear
	# Force workers to restart
	docker-compose exec -u www-data app php bin/console messenger:stop-workers

migrate:
	docker-compose exec -u www-data app php bin/console doctrine:migrations:migrate
	docker-compose exec -u www-data app php bin/console themes:migrate ./src/Resources/config.yml

pull:
	git pull
	cd lib/DocGenerator && git pull
	cd lib/Documents && git pull
	cd lib/DtsGenerator && git pull
	cd lib/EntityGenerator && git pull
	cd lib/Jwt && git pull
	cd lib/Markdown && git pull
	cd lib/Models && git pull
	cd lib/OpenId && git pull
	cd lib/Random && git pull
	cd lib/RoadizCompatBundle && git pull
	cd lib/RoadizCoreBundle && git pull
	cd lib/RoadizFontBundle && git pull
	cd lib/RoadizRozierBundle && git pull
	cd lib/RoadizUserBundle && git pull
	cd lib/Rozier && git pull

push:
	git push
	cd lib/DocGenerator && git push
	cd lib/Documents && git push
	cd lib/DtsGenerator && git push
	cd lib/EntityGenerator && git push
	cd lib/Jwt && git push
	cd lib/Markdown && git push
	cd lib/Models && git push
	cd lib/OpenId && git push
	cd lib/Random && git push
	cd lib/RoadizCompatBundle && git push
	cd lib/RoadizCoreBundle && git push
	cd lib/RoadizFontBundle && git push
	cd lib/RoadizRozierBundle && git push
	cd lib/RoadizUserBundle && git push
	cd lib/Rozier && git push
