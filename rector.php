<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/lib/RoadizCoreBundle/src',
        __DIR__ . '/lib/RoadizUserBundle/src',
        __DIR__ . '/lib/RoadizRozierBundle/src',
    ]);

    // define sets of rules
    $rectorConfig->sets([
//        LevelSetList::UP_TO_PHP_80,
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
    ]);
};
