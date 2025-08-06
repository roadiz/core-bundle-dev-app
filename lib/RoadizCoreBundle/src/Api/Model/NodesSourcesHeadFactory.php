<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class NodesSourcesHeadFactory implements NodesSourcesHeadFactoryInterface
{
    public function __construct(
        private Settings $settingsBag,
        private UrlGeneratorInterface $urlGenerator,
        private HandlerFactoryInterface $handlerFactory,
    ) {
    }

    #[\Override]
    public function createForNodeSource(NodesSources $nodesSources): NodesSourcesHeadInterface
    {
        return new NodesSourcesHead(
            $nodesSources,
            $this->settingsBag,
            $this->urlGenerator,
            $this->handlerFactory,
            $nodesSources->getTranslation()
        );
    }

    #[\Override]
    public function createForTranslation(TranslationInterface $translation): NodesSourcesHeadInterface
    {
        return new NodesSourcesHead(
            null,
            $this->settingsBag,
            $this->urlGenerator,
            $this->handlerFactory,
            $translation
        );
    }
}
