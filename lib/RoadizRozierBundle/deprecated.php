<?php

declare(strict_types=1);

use RZ\Roadiz\RozierBundle\Aliases;

foreach (Aliases::getAliases() as $className => $alias) {
    \class_alias($className, $alias);
}
