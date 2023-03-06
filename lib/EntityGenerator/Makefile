test:
	vendor/bin/phpcbf --report=full --report-file=./report.txt --extensions=php --warning-severity=0 --standard=PSR12 -p ./src
	vendor/bin/phpstan analyse -c phpstan.neon
	vendor/bin/atoum -f tests/units/*
