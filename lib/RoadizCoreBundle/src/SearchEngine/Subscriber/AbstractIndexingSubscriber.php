<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractIndexingSubscriber implements EventSubscriberInterface
{
    protected function flattenTextCollection(array $collection): string
    {
        return trim(implode(PHP_EOL, array_filter(array_unique($collection))));
    }

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
            isset($geoJson['type'])
            && 'Feature' === $geoJson['type']
            && isset($geoJson['geometry']['coordinates'])
        ) {
            return $geoJson['geometry']['coordinates'][1].','.$geoJson['geometry']['coordinates'][0];
        }

        return null;
    }

    protected function formatGeoJsonFeatureCollection(mixed $geoJson): ?array
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
            isset($geoJson['type'])
            && 'FeatureCollection' === $geoJson['type']
            && isset($geoJson['features'])
            && \count($geoJson['features']) > 0
        ) {
            return array_filter(array_map(function ($feature) {
                return $this->formatGeoJsonFeature($feature);
            }, $geoJson['features']));
        }

        return null;
    }
}
