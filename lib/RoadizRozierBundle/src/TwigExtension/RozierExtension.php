<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TwigExtension;

use RZ\Roadiz\Core\AbstractEntities\NodeInterface;
use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\StackType;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\RozierBundle\Breadcrumbs\BreadcrumbsItem;
use RZ\Roadiz\RozierBundle\Breadcrumbs\BreadcrumbsItemFactoryInterface;
use RZ\Roadiz\RozierBundle\RozierServiceRegistry;
use RZ\Roadiz\RozierBundle\TranslateAssistant\NullTranslateAssistant;
use RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantInterface;
use RZ\Roadiz\RozierBundle\Vite\JsonManifestResolver;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

final class RozierExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly RozierServiceRegistry $rozierServiceRegistry,
        private readonly DecoratedNodeTypes $nodeTypesBag,
        private readonly JsonManifestResolver $manifestResolver,
        private readonly TranslateAssistantInterface $translateAssistant,
        private readonly BreadcrumbsItemFactoryInterface $breadcrumbItemFactory,
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
            'translateAssistantEnabled' => !$this->translateAssistant instanceof NullTranslateAssistant,
            'translateAssistantSupportRephrase' => $this->translateAssistant->supportRephrase(),
        ];
    }

    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getNodeType', $this->getNodeType(...)),
            new TwigFunction('getBreadcrumbsItem', $this->getBreadcrumbsItem(...)),
            new TwigFunction('manifest_script_tags', $this->getManifestScriptTags(...), ['is_safe' => ['html']]),
            new TwigFunction('manifest_style_tags', $this->getManifestStyleTags(...), ['is_safe' => ['html']]),
            new TwigFunction('manifest_preload_tags', $this->getManifestPreloadTags(...), ['is_safe' => ['html']]),
        ];
    }

    public function getBreadcrumbsItem(?object $item): ?BreadcrumbsItem
    {
        return $this->breadcrumbItemFactory->createBreadcrumbsItem($item);
    }

    public function getManifestScriptTags(string $name): string
    {
        return implode('', array_map(fn ($cssFilePath) => sprintf(
            '<script async type="module" src="%s"></script>',
            htmlspecialchars((string) $cssFilePath, ENT_QUOTES, 'UTF-8')
        ), $this->manifestResolver->getEntrypointScriptFiles($name)));
    }

    public function getManifestStyleTags(string $name): string
    {
        return implode('', array_map(fn ($cssFilePath) => sprintf(
            '<link rel="stylesheet" href="%s">',
            htmlspecialchars((string) $cssFilePath, ENT_QUOTES, 'UTF-8')
        ), $this->manifestResolver->getEntrypointCssFiles($name)));
    }

    public function getManifestPreloadTags(string $name): string
    {
        return implode('', array_map(fn ($preloadFilePath) => sprintf(
            '<link rel="preload" href="%s" as="%s">',
            htmlspecialchars((string) $preloadFilePath['href'], ENT_QUOTES, 'UTF-8'),
            htmlspecialchars((string) $preloadFilePath['as'], ENT_QUOTES, 'UTF-8')
        ), $this->manifestResolver->getEntrypointPreloadFiles($name)));
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
