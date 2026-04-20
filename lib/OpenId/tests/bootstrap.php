<?php

declare(strict_types=1);

if (file_exists(dirname(__DIR__).'/vendor/autoload.php')) {
    require dirname(__DIR__).'/vendor/autoload.php';
} elseif (file_exists(dirname(__DIR__).'/../../vendor/autoload.php')) {
    require dirname(__DIR__).'/../../vendor/autoload.php';
}
