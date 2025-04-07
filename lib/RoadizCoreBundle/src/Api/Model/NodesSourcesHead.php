<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\EntityHandler\NodesSourcesHandler;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Attribute as Serializer;

class NodesSourcesHead implements NodesSourcesHeadInterface
{
    #[Serializer\Ignore]
    protected ?array $seo = null;

    public function __construct(
        #[Serializer\Ignore]
        protected readonly ?NodesSources $nodesSource,
        #[Serializer\Ignore]
        protected readonly Settings $settingsBag,
        #[Serializer\Ignore]
        protected readonly UrlGeneratorInterface $urlGenerator,
        #[Serializer\Ignore]
        protected readonly HandlerFactoryInterface $handlerFactory,
        #[Serializer\Ignore]
        protected readonly TranslationInterface $defaultTranslation,
    ) {
    }

    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getGoogleAnalytics(): ?string
    {
        return $this->settingsBag->get('universal_analytics_id', null) ?? null;
    }

    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getGoogleTagManager(): ?string
    {
        return $this->settingsBag->get('google_tag_manager_id', null) ?? null;
    }

    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getMatomoTagManager(): ?string
    {
        return $this->settingsBag->get('matomo_tag_manager_id', null) ?? null;
    }

    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getMatomoUrl(): ?string
    {
        return $this->settingsBag->get('matomo_url', null) ?? null;
    }

    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getMatomoSiteId(): ?string
    {
        return $this->settingsBag->get('matomo_site_id', null) ?? null;
    }

    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getSiteName(): ?string
    {
        return $this->settingsBag->get('site_name', null) ?? null;
    }

    #[Serializer\Ignore]
    protected function getDefaultSeo(): array
    {
        if (null !== $this->nodesSource) {
            $nodesSourcesHandler = $this->handlerFactory->getHandler($this->nodesSource);
            if ($nodesSourcesHandler instanceof NodesSourcesHandler) {
                return $nodesSourcesHandler->getSEO();
            }
        }

        return [
            'title' => $this->settingsBag->get('site_name'),
            'description' => $this->settingsBag->get('seo_description'),
        ];
    }

    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getMetaTitle(): ?string
    {
        if (null === $this->seo) {
            $this->seo = $this->getDefaultSeo();
        }

        return $this->seo['title'];
    }

    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getMetaDescription(): ?string
    {
        if (null === $this->seo) {
            $this->seo = $this->getDefaultSeo();
        }

        return $this->seo['description'];
    }

    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function isNoIndex(): bool
    {
        if (null !== $this->nodesSource) {
            return $this->nodesSource->isNoIndex();
        }

        return false;
    }

    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getMainColor(): ?string
    {
        return $this->settingsBag->get('main_color', null) ?? null;
    }

    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getFacebookUrl(): ?string
    {
        return $this->settingsBag->get('facebook_url', null) ?? null;
    }

    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getInstagramUrl(): ?string
    {
        return $this->settingsBag->get('instagram_url', null) ?? null;
    }

    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getTwitterUrl(): ?string
    {
        return $this->settingsBag->get('twitter_url', null) ?? null;
    }

    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getYoutubeUrl(): ?string
    {
        return $this->settingsBag->get('youtube_url', null) ?? null;
    }

    #[Serializer\Groups(['nodes_sources_single', 'walker'])]
    public function getLinkedinUrl(): ?string
    {
        return $this->settingsBag->get('linkedin_url', null) ?? null;
    }

    #[Serializer\Groups(['nodes_sources_single', 'walker'])]
    public function getSpotifyUrl(): ?string
    {
        return $this->settingsBag->get('spotify_url', null) ?? null;
    }

    #[Serializer\Groups(['nodes_sources_single', 'walker'])]
    public function getSoundcloudUrl(): ?string
    {
        return $this->settingsBag->get('soundcloud_url', null) ?? null;
    }

    #[Serializer\Groups(['nodes_sources_single', 'walker'])]
    public function getTikTokUrl(): ?string
    {
        return $this->settingsBag->get('tiktok_url', null) ?? null;
    }

    #[Serializer\Groups(['web_response', 'nodes_sources_single'])]
    public function getShareImage(): ?DocumentInterface
    {
        if (
            null !== $this->nodesSource
            && method_exists($this->nodesSource, 'getHeaderImage')
            && isset($this->nodesSource->getHeaderImage()[0])
        ) {
            return $this->nodesSource->getHeaderImage()[0];
        }
        if (
            null !== $this->nodesSource
            && method_exists($this->nodesSource, 'getImage')
            && isset($this->nodesSource->getImage()[0])
        ) {
            return $this->nodesSource->getImage()[0];
        }

        return $this->settingsBag->getDocument('share_image') ?? null;
    }

    #[Serializer\Ignore()]
    public function getTranslation(): TranslationInterface
    {
        if (null !== $this->nodesSource) {
            return $this->nodesSource->getTranslation();
        }

        return $this->defaultTranslation;
    }
}
