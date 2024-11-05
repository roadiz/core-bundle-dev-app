<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class RoadizExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly Settings $settingsBag,
        private readonly NodeTypes $nodeTypesBag,
        private readonly PreviewResolverInterface $previewResolver,
        private readonly NodeChrootResolver $chrootResolver,
        private readonly string $cmsVersion,
        private readonly string $cmsVersionPrefix,
        private readonly bool $hideRoadizVersion,
        private readonly int $maxVersionsShowed,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'cms_version' => !$this->hideRoadizVersion ? $this->cmsVersion : null,
            'cms_prefix' => !$this->hideRoadizVersion ? $this->cmsVersionPrefix : null,
            'max_versions_showed' => $this->maxVersionsShowed,
            'help_external_url' => 'http://docs.roadiz.io',
            'is_preview' => $this->previewResolver->isPreview(),
            'bags' => [
                'settings' => $this->settingsBag,
                'nodeTypes' => $this->nodeTypesBag,
            ],
            'chroot_resolver' => $this->chrootResolver,
            'main_color' => $this->settingsBag->get('main_color'),
            'meta' => [
                'siteName' => $this->settingsBag->get('site_name'),
                'siteCopyright' => $this->settingsBag->get('site_copyright'),
                'siteDescription' => $this->settingsBag->get('seo_description'),
            ],
        ];
    }
}
