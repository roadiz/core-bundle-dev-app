cache :
	php bin/console cache:clear

# Migrate your configured theme, update DB and empty caches.
migrate:
	@echo "✅\t${GREEN}Update schema node-types${NC}" >&2;
	php bin/console themes:migrate /Themes/${THEME}/${THEME}App;
	make cache;

test:
	#php -d "memory_limit=-1" vendor/bin/phpcs --report=full --report-file=./report.txt -p ./src
	php -d "memory_limit=-1" vendor/bin/phpcs --report=full --report-file=./report.txt -p ./lib/RoadizCoreBundle/src
	php -d "memory_limit=-1" vendor/bin/phpcs --report=full --report-file=./report.txt -p ./lib/RoadizCompatBundle/src
	php -d "memory_limit=-1" vendor/bin/phpcs --report=full --report-file=./report.txt -p ./lib/RoadizRozierBundle/src
	php -d "memory_limit=-1" vendor/bin/phpstan analyse -c phpstan.neon
	php -d "memory_limit=-1" bin/console lint:twig
