<?php

declare(strict_types=1);

use RZ\Roadiz\CoreBundle\Entity\AbstractField;

if (!class_exists(RZ\Roadiz\Core\AbstractEntities\AbstractField::class)) {
    \class_alias(AbstractField::class, RZ\Roadiz\Core\AbstractEntities\AbstractField::class);
}
