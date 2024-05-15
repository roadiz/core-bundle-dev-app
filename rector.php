<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Symfony\Set\JMSSetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/lib/DocGenerator/src',
        __DIR__ . '/lib/Documents/src',
        __DIR__ . '/lib/DtsGenerator/src',
        __DIR__ . '/lib/EntityGenerator/src',
        __DIR__ . '/lib/Jwt/src',
        __DIR__ . '/lib/Markdown/src',
        __DIR__ . '/lib/Models/src',
        __DIR__ . '/lib/OpenId/src',
        __DIR__ . '/lib/Random/src',
        __DIR__ . '/lib/RoadizCompatBundle/src',
        __DIR__ . '/lib/RoadizCoreBundle/src',
        __DIR__ . '/lib/RoadizFontBundle/src',
        __DIR__ . '/lib/RoadizRozierBundle/src',
        __DIR__ . '/lib/RoadizTwoFactorBundle/src',
        __DIR__ . '/lib/RoadizUserBundle/src',
        __DIR__ . '/lib/Rozier/src',
    ]);

    // define sets of rules
    $rectorConfig->sets([
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        JMSSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
    ]);
};
