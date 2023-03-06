<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\String\UnicodeString;

/**
 * Route handling methods.
 */
class RouteHandler
{
    public static function getBaseRoute(string $path): string
    {
        if ((new UnicodeString($path))->endsWith('Locale')) {
            $path = StringHandler::replaceLast("Locale", "", $path);
        }
        return $path;
    }
}
