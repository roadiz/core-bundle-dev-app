<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class RoadizExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly Settings $settingsBag,
        private readonly DecoratedNodeTypes $nodeTypesBag,
        private readonly PreviewResolverInterface $previewResolver,
        private readonly NodeChrootResolver $chrootResolver,
        private readonly DocumentUrlGeneratorInterface $documentUrlGenerator,
        private readonly string $cmsVersion,
        private readonly string $cmsVersionPrefix,
        private readonly bool $hideRoadizVersion,
        private readonly int $maxVersionsShowed,
        private readonly ?string $helpExternalUrl,
    ) {
    }

    #[\Override]
    public function getGlobals(): array
    {
        $emailHeaderImage = null;
        $adminImage = $this->settingsBag->getDocument('admin_image');
        if ($adminImage instanceof DocumentInterface && null !== $this->documentUrlGenerator) {
            $this->documentUrlGenerator->setDocument($adminImage);
            $emailHeaderImage = $this->documentUrlGenerator->getUrl(true);
        }

        return [
            'cms_version' => !$this->hideRoadizVersion ? $this->cmsVersion : null,
            'cms_prefix' => !$this->hideRoadizVersion ? $this->cmsVersionPrefix : null,
            'max_versions_showed' => $this->maxVersionsShowed,
            'help_external_url' => $this->helpExternalUrl,
            'is_preview' => $this->previewResolver->isPreview(),
            'bags' => [
                'settings' => $this->settingsBag,
                'nodeTypes' => $this->nodeTypesBag,
            ],
            'chroot_resolver' => $this->chrootResolver,
            'main_color' => $this->settingsBag->get('main_color'),
            'support_email_address' => $this->settingsBag->get('support_email_address'),
            'email_disclaimer' => $this->settingsBag->get('email_disclaimer'),
            'email_header_image' => $emailHeaderImage,
            'meta' => [
                'siteName' => $this->settingsBag->get('site_name'),
                'backofficeName' => $this->settingsBag->get('site_name').' backstage',
                'siteCopyright' => $this->settingsBag->get('site_copyright'),
                'siteDescription' => $this->settingsBag->get('seo_description'),
            ],
        ];
    }
}
