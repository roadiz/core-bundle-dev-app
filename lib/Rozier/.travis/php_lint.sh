#!/bin/sh -x
vendor/bin/phpcs --report=full --report-file=./report.txt -p ./ || exit 1;
vendor/bin/phpstan analyse -c phpstan.neon || exit 1;
#vendor/bin/console lint:twig || exit 1;
#vendor/bin/console lint:twig src/Resources/views || exit 1;
