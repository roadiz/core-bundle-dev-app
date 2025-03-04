<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

use Intervention\Image\Image;

final readonly class AverageColorResolver
{
    public function getAverageColor(Image $image): string
    {
        $colorArray = $this->getAverageColorAsArray($image);

        return sprintf(
            '#%02x%02x%02x',
            $colorArray[0],
            $colorArray[1],
            $colorArray[2]
        );
    }

    public function getAverageColorAsArray(Image $image): array
    {
        $image->resize(1, 1);
        /** @var array $array */
        $array = $image->pickColor(0, 0);

        return $array;
    }
}
