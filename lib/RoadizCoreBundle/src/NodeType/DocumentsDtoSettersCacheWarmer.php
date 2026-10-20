<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\NodeType;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\EntityGenerator\Field\DocumentsFieldGenerator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Reminds developers to regenerate node-source entities generated before documents DTO setters existed:
 * NodesSourcesPrefetcher skips their documents fields.
 */
final readonly class DocumentsDtoSettersCacheWarmer implements CacheWarmerInterface
{
    /**
     * @param array<string, mixed> $entityGeneratorOptions
     */
    public function __construct(
        private NodeTypes $nodeTypesBag,
        private NodeTypeClassLocatorInterface $nodeTypeClassLocator,
        private LoggerInterface $logger,
        #[Autowire(param: 'roadiz_core.entity_generator_factory.options')]
        private array $entityGeneratorOptions,
    ) {
    }

    #[\Override]
    public function isOptional(): bool
    {
        return true;
    }

    #[\Override]
    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        // Without DocumentDto, documents setters are never generated.
        if (true !== ($this->entityGeneratorOptions['use_document_dto'] ?? false)) {
            return [];
        }

        $outdatedClasses = [];
        /** @var NodeType $nodeType */
        foreach ($this->nodeTypesBag->all() as $nodeType) {
            $className = $this->nodeTypeClassLocator->getSourceEntityFullQualifiedClassName($nodeType);
            if (!class_exists($className)) {
                continue;
            }
            foreach ($nodeType->getFields() as $field) {
                if ($field->isDocuments() && !method_exists($className, DocumentsFieldGenerator::getDtoSetterName($field))) {
                    $outdatedClasses[] = $className;
                    break;
                }
            }
        }

        if ([] !== $outdatedClasses) {
            $this->logger->warning(sprintf(
                'Documents fields of %s are not prefetched in API responses. Regenerate node-source entities with "bin/console generate:nsentities".',
                implode(', ', $outdatedClasses)
            ));
        }

        return [];
    }
}
