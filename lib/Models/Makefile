test:
	vendor/bin/phpunit -v --bootstrap=tests/bootstrap.php --whitelist ./src --coverage-clover ./build/logs/clover.xml tests/
	vendor/bin/phpcbf -p ./src
	vendor/bin/phpstan analyse -c phpstan.neon

phpcs:
	vendor/bin/phpcs --report=full --report-file=./report.txt -p ./src

phpcbf:
	vendor/bin/phpcbf -p ./src
