<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\EntityApi\NodeSourceApi;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class NodesSourcesHeadFactory implements NodesSourcesHeadFactoryInterface
{
    public function __construct(
        private readonly Settings $settingsBag,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly NodeSourceApi $nodeSourceApi,
        private readonly HandlerFactoryInterface $handlerFactory
    ) {
    }

    public function createForNodeSource(NodesSources $nodesSources): NodesSourcesHeadInterface
    {
        return new NodesSourcesHead(
            $nodesSources,
            $this->settingsBag,
            $this->urlGenerator,
            $this->nodeSourceApi,
            $this->handlerFactory,
            $nodesSources->getTranslation()
        );
    }

    public function createForTranslation(TranslationInterface $translation): NodesSourcesHeadInterface
    {
        return new NodesSourcesHead(
            null,
            $this->settingsBag,
            $this->urlGenerator,
            $this->nodeSourceApi,
            $this->handlerFactory,
            $translation
        );
    }
}
