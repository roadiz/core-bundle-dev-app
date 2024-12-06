<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Contracts\EventDispatcher\Event;

abstract class FilterTranslationEvent extends Event
{
    public function __construct(protected readonly Translation $translation)
    {
    }

    public function getTranslation(): Translation
    {
        return $this->translation;
    }
}
