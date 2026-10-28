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
        private readonly ?string $customPublicScheme,
        private readonly ?string $customPreviewScheme,
        private readonly ?string $leafletMapTileUrl,
        private readonly ?string $mapsDefaultLocation,
        private readonly ?string $projectLogoUrl,
    ) {
    }

    /**
     * `main_color` is rendered unescaped into raw CSS (custom property values, inline
     * `style` attributes) as well as a JS string literal, contexts where HTML-entity or
     * JS escaping either does nothing (HTML entities are not decoded inside a `<style>`
     * block) or produces invalid CSS. Since a color value never legitimately needs any of
     * the characters that could break out of those contexts, only allow the character set
     * used by hex colors, named colors, and CSS color functions (`rgb()`, `hsl()`, ...) —
     * anything else is dropped instead of rendered.
     */
    public static function isSafeCssColorValue(mixed $value): bool
    {
        if (!\is_string($value) || '' === $value) {
            return false;
        }

        return 1 === preg_match('/^[a-zA-Z0-9#(),.%\s-]+$/', $value);
    }

    #[\Override]
    public function getGlobals(): array
    {
        $projectLogoUrl = $this->projectLogoUrl;
        if (empty($projectLogoUrl)) {
            $adminImage = $this->settingsBag->getDocument('admin_image');
            if ($adminImage instanceof DocumentInterface) {
                $this->documentUrlGenerator->setDocument($adminImage);
                $projectLogoUrl = $this->documentUrlGenerator->getUrl(true);
            }
        }

        $mainColor = $this->settingsBag->get('main_color');
        if (!self::isSafeCssColorValue($mainColor)) {
            $mainColor = null;
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
            'main_color' => $mainColor,
            'support_email_address' => $this->settingsBag->get('support_email_address'),
            'email_disclaimer' => $this->settingsBag->get('email_disclaimer'),
            'custom_public_scheme' => $this->customPublicScheme,
            'custom_preview_scheme' => $this->customPreviewScheme,
            'leaflet_map_tile_url' => $this->leafletMapTileUrl,
            'maps_default_location' => $this->mapsDefaultLocation,
            'project_logo_url' => $projectLogoUrl,
            'meta' => [
                'siteName' => $this->settingsBag->get('site_name'),
                'backofficeName' => $this->settingsBag->get('site_name').' backstage',
                'siteCopyright' => $this->settingsBag->get('site_copyright'),
                'siteDescription' => $this->settingsBag->get('seo_description'),
            ],
        ];
    }
}
