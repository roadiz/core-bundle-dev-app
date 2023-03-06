test:
	vendor/bin/atoum -d tests
	vendor/bin/phpstan analyse -c phpstan.neon
	vendor/bin/phpcs --report=full --report-file=./report.txt -p ./src

dev-test:
	vendor/bin/atoum -d tests -l

phpcs:
	vendor/bin/phpcs --report=full --report-file=./report.txt -p ./src

phpcbf:
	vendor/bin/phpcbf --report=full --report-file=./report.txt -p ./src
