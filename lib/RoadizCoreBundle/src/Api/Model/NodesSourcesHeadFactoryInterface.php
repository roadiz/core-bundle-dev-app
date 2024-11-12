<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;

interface NodesSourcesHeadFactoryInterface
{
    public function createForTranslation(TranslationInterface $translation): NodesSourcesHeadInterface;

    public function createForNodeSource(NodesSources $nodesSources): NodesSourcesHeadInterface;
}
