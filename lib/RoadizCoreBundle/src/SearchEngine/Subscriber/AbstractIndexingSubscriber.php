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

    protected function formatGeoJsonFeature(mixed $geoJson): ?string
    {
        if (null === $geoJson) {
            return null;
        }
        if (\is_string($geoJson)) {
            $geoJson = \json_decode($geoJson, true);
        }
        if (!\is_array($geoJson)) {
            return null;
        }

        if (
            isset($geoJson['type']) &&
            $geoJson['type'] === 'Feature' &&
            isset($geoJson['geometry']['coordinates'])
        ) {
            return $geoJson['geometry']['coordinates'][1] . ',' . $geoJson['geometry']['coordinates'][0];
        }
        return null;
    }
}
