test:
	vendor/bin/phpunit -v --whitelist ./src --coverage-clover ./build/logs/clover.xml src/Test
	vendor/bin/phpstan analyse -c phpstan.neon
	vendor/bin/phpcs --report=full --report-file=./report.txt -p ./src

dev-test:
	vendor/bin/phpunit -v --whitelist ./src --coverage-clover ./build/logs/clover.xml src/Test

phpcs:
	vendor/bin/phpcs --report=full --report-file=./report.txt -p ./src

phpcbf:
	vendor/bin/phpcbf --report=full --report-file=./report.txt -p ./src
