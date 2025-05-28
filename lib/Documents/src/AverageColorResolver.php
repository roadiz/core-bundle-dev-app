<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

use Intervention\Image\Interfaces\ImageInterface;

final readonly class AverageColorResolver
{
    /**
     * Get the average color of an image by resampling the image to 1x1 pixel and pick this pixel color.
     * Then only use the RGB channels by picking only the 7 first hex chars to get rid of alpha channel.
     */
    public function getAverageColor(ImageInterface $image): string
    {
        return substr($image->resize(1, 1)->pickColor(0, 0)->toHex('#'), 0, 7);
    }
}
