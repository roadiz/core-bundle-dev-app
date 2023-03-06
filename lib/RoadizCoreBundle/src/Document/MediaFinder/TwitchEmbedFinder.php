<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MediaFinder;

use RZ\Roadiz\Documents\MediaFinders\AbstractTwitchEmbedFinder;

class TwitchEmbedFinder extends AbstractTwitchEmbedFinder
{
    use EmbedFinderTrait;
}
