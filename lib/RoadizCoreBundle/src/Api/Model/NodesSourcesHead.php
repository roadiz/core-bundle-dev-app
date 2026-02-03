<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use ApiPlatform\Metadata\ApiResource;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\EntityHandler\NodesSourcesHandler;
use RZ\Roadiz\Documents\Models\BaseDocumentInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Attribute as Serializer;

#[ApiResource(operations: [])]
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

    /**
     * @deprecated Since 2.6, provide universal_analytics_id in your CommonContent resource instead.
     */
    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getGoogleAnalytics(): ?string
    {
        return $this->settingsBag->get('universal_analytics_id', null) ?? null;
    }

    /**
     * @deprecated Since 2.6, provide google_tag_manager_id in your CommonContent resource instead.
     */
    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getGoogleTagManager(): ?string
    {
        return $this->settingsBag->get('google_tag_manager_id', null) ?? null;
    }

    /**
     * @deprecated Since 2.6, provide matomo_tag_manager_id in your CommonContent resource instead.
     */
    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getMatomoTagManager(): ?string
    {
        return $this->settingsBag->get('matomo_tag_manager_id', null) ?? null;
    }

    /**
     * @deprecated Since 2.6, provide matomo_url in your CommonContent resource instead.
     */
    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getMatomoUrl(): ?string
    {
        return $this->settingsBag->get('matomo_url', null) ?? null;
    }

    /**
     * @deprecated Since 2.6, provide matomo_site_id in your CommonContent resource instead.
     */
    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getMatomoSiteId(): ?string
    {
        return $this->settingsBag->get('matomo_site_id', null) ?? null;
    }

    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    #[\Override]
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
    #[\Override]
    public function getMetaTitle(): ?string
    {
        if (null === $this->seo) {
            $this->seo = $this->getDefaultSeo();
        }

        return $this->seo['title'];
    }

    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    #[\Override]
    public function getMetaDescription(): ?string
    {
        if (null === $this->seo) {
            $this->seo = $this->getDefaultSeo();
        }

        return $this->seo['description'];
    }

    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    #[\Override]
    public function isNoIndex(): bool
    {
        if (null !== $this->nodesSource) {
            return $this->nodesSource->isNoIndex();
        }

        return false;
    }

    /**
     * @deprecated Since 2.6, provide main_color in your CommonContent resource instead.
     */
    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getMainColor(): ?string
    {
        return $this->settingsBag->get('main_color', null) ?? null;
    }

    /**
     * @deprecated Since 2.6, provide facebook_url in your CommonContent resource instead.
     */
    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getFacebookUrl(): ?string
    {
        return $this->settingsBag->get('facebook_url', null) ?? null;
    }

    /**
     * @deprecated Since 2.6, provide instagram_url in your CommonContent resource instead.
     */
    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getInstagramUrl(): ?string
    {
        return $this->settingsBag->get('instagram_url', null) ?? null;
    }

    /**
     * @deprecated Since 2.6, provide twitter_url in your CommonContent resource instead.
     */
    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getTwitterUrl(): ?string
    {
        return $this->settingsBag->get('twitter_url', null) ?? null;
    }

    /**
     * @deprecated Since 2.6, provide youtube_url in your CommonContent resource instead.
     */
    #[Serializer\Groups(['web_response', 'nodes_sources_single', 'walker'])]
    public function getYoutubeUrl(): ?string
    {
        return $this->settingsBag->get('youtube_url', null) ?? null;
    }

    /**
     * @deprecated Since 2.6, provide linkedin_url in your CommonContent resource instead.
     */
    #[Serializer\Groups(['nodes_sources_single', 'walker'])]
    public function getLinkedinUrl(): ?string
    {
        return $this->settingsBag->get('linkedin_url', null) ?? null;
    }

    /**
     * @deprecated Since 2.6, provide spotify_url in your CommonContent resource instead.
     */
    #[Serializer\Groups(['nodes_sources_single', 'walker'])]
    public function getSpotifyUrl(): ?string
    {
        return $this->settingsBag->get('spotify_url', null) ?? null;
    }

    /**
     * @deprecated Since 2.6, provide soundcloud_url in your CommonContent resource instead.
     */
    #[Serializer\Groups(['nodes_sources_single', 'walker'])]
    public function getSoundcloudUrl(): ?string
    {
        return $this->settingsBag->get('soundcloud_url', null) ?? null;
    }

    /**
     * @deprecated Since 2.6, provide tiktok_url in your CommonContent resource instead.
     */
    #[Serializer\Groups(['nodes_sources_single', 'walker'])]
    public function getTikTokUrl(): ?string
    {
        return $this->settingsBag->get('tiktok_url', null) ?? null;
    }

    #[Serializer\Groups(['web_response', 'nodes_sources_single'])]
    #[\Override]
    public function getShareImage(): ?BaseDocumentInterface
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
    #[\Override]
    public function getTranslation(): TranslationInterface
    {
        if (null !== $this->nodesSource) {
            return $this->nodesSource->getTranslation();
        }

        return $this->defaultTranslation;
    }
}
