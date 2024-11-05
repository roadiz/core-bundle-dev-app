<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

interface RandomImageFinder
{
    /**
     * @return array|null a data feed for a random image
     */
    public function getRandom(array $options = []): ?array;

    /**
     * @return array|bool|mixed A data feed for a random image by keyword
     */
    public function getRandomBySearch(string $keyword, array $options = []): mixed;
}
