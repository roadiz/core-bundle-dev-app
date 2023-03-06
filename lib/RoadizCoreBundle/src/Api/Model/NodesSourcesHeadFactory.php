<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\EntityApi\NodeSourceApi;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class NodesSourcesHeadFactory
{
    private Settings $settingsBag;
    private UrlGeneratorInterface $urlGenerator;
    private NodeSourceApi $nodeSourceApi;
    private HandlerFactoryInterface $handlerFactory;

    /**
     * @param Settings $settingsBag
     * @param UrlGeneratorInterface $urlGenerator
     * @param NodeSourceApi $nodeSourceApi
     * @param HandlerFactoryInterface $handlerFactory
     */
    public function __construct(
        Settings $settingsBag,
        UrlGeneratorInterface $urlGenerator,
        NodeSourceApi $nodeSourceApi,
        HandlerFactoryInterface $handlerFactory
    ) {
        $this->settingsBag = $settingsBag;
        $this->urlGenerator = $urlGenerator;
        $this->nodeSourceApi = $nodeSourceApi;
        $this->handlerFactory = $handlerFactory;
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
