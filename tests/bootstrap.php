<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

/*
 * Install Roadiz database and fixtures
 * for tests
 */
$dbServerExists = passthru(sprintf(
    'APP_ENV=%s php "%s/../bin/console" --env=test doctrine:database:create --if-not-exists',
    $_ENV['APP_ENV'],
    __DIR__
), $resultCode);

/*
 * If no database server is available, we skip installation
 */
if (0 === $resultCode) {
    passthru(sprintf(
        'APP_ENV=%s php "%s/../bin/console" --env=test doctrine:migrations:migrate -n',
        $_ENV['APP_ENV'],
        __DIR__
    ));

    passthru(sprintf(
        'APP_ENV=%s php "%s/../bin/console" --env=test doctrine:fixtures:load -n',
        $_ENV['APP_ENV'],
        __DIR__
    ));
}
