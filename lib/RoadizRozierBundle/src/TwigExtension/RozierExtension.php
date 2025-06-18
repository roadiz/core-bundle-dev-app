<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TwigExtension;

use RZ\Roadiz\Core\AbstractEntities\NodeInterface;
use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\StackType;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use Symfony\Component\Asset\Package;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Themes\Rozier\RozierServiceRegistry;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

final class RozierExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly RozierServiceRegistry $rozierServiceRegistry,
        private readonly DecoratedNodeTypes $nodeTypesBag,
        #[Autowire(param: 'roadiz_rozier.manifest_path')]
        private readonly string $manifestPath,
        #[Autowire(service: 'roadiz_rozier.assets._package.Rozier')]
        private readonly Package $rozierPackage,
    ) {
    }

    #[\Override]
    public function getGlobals(): array
    {
        return [
            'rozier' => $this->rozierServiceRegistry,
            'nodeStatuses' => NodeStatus::allLabelsAndValues(),
            'thumbnailFormat' => [
                'quality' => 50,
                'crop' => '1:1',
                'width' => 128,
                'sharpen' => 5,
                'inline' => false,
                'picture' => true,
                'controls' => false,
                'loading' => 'lazy',
            ],
        ];
    }

    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getNodeType', $this->getNodeType(...)),
            new TwigFunction('manifest_script_tag', $this->getManifestScriptTag(...), ['is_safe' => ['html']]),
            new TwigFunction('manifest_style_tag', $this->getManifestStyleTag(...), ['is_safe' => ['html']]),
        ];
    }

    private function getManifest(): array
    {
        if (!file_exists($this->manifestPath)) {
            throw new \RuntimeException(sprintf('%s manifest not found', $this->manifestPath));
        }
        return json_decode(file_get_contents($this->manifestPath), true, flags: JSON_THROW_ON_ERROR);
    }

    public function getManifestScriptTag(string $name): string
    {
        $manifest = $this->getManifest();

        foreach ($manifest as $value) {
            if (is_array($value)
                && isset($value['name'])
                && isset($value['file'])
                && $value['name'] === $name
            ) {
                return sprintf(
                    '<script async type="module" src="%s"></script>',
                    htmlspecialchars($this->rozierPackage->getUrl($value['file']), ENT_QUOTES, 'UTF-8')
                );
            }
        }

        throw new \RuntimeException(sprintf('%s file not found in manifest.json', $name));
    }

    public function getManifestStyleTag(string $name): string
    {
        $manifest = $this->getManifest();

        foreach ($manifest as $value) {
            if (is_array($value)
                && isset($value['name'])
                && isset($value['css'])
                && is_array($value['css'])
                && $value['name'] === $name
            ) {
                return implode('', array_map(fn ($cssFile) => sprintf(
                    '<link rel="stylesheet" href="%s">',
                    htmlspecialchars($this->rozierPackage->getUrl($cssFile), ENT_QUOTES, 'UTF-8')
                ), $value['css']));
            }
        }

        throw new \RuntimeException(sprintf('%s file not found in manifest.json', $name));
    }

    public function getNodeType(mixed $object): ?NodeType
    {
        if (null === $object) {
            return null;
        }

        if (is_string($object)) {
            return $this->nodeTypesBag->get($object);
        }

        if ($object instanceof StackType) {
            return $this->nodeTypesBag->get($object->getNodeTypeName());
        }

        if ($object instanceof NodeInterface) {
            return $this->nodeTypesBag->get($object->getNodeTypeName());
        }

        if ($object instanceof NodesSources) {
            return $this->nodeTypesBag->get($object->getNodeTypeName());
        }

        throw new \RuntimeException('Unexpected object type');
    }
}
