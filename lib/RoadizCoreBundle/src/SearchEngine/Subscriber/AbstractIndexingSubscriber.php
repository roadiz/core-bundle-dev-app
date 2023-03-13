<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Subscriber;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

abstract class AbstractIndexingSubscriber implements EventSubscriberInterface
{
    protected function formatDateTimeToUTC(\DateTimeInterface $dateTime): string
    {
        return gmdate('Y-m-d\TH:i:s\Z', $dateTime->getTimestamp());
    }
}
