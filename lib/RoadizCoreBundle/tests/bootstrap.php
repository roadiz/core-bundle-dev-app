<?php

use Symfony\Component\Dotenv\Dotenv;

// Allow testing in Monorepo environment
if (file_exists(dirname(__DIR__).'/vendor/autoload.php')) {
    require dirname(__DIR__).'/vendor/autoload.php';
} elseif (file_exists(dirname(__DIR__).'/../../vendor/autoload.php')) {
    require dirname(__DIR__).'/../../vendor/autoload.php';
}

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}
