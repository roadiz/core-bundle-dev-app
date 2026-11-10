<?php

declare(strict_types=1);

namespace App\Story;

use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesToNodes;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Node\UniqueNodeGenerator;
use Zenstruck\Foundry\Story;

abstract class AbstractNodeStory extends Story
{
    public function __construct(
        protected readonly UniqueNodeGenerator $uniqueNodeGenerator,
        protected readonly NodeTypes $nodeTypesBag,
        protected readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public static function expect(mixed $value, string $class): object
    {
        if (!$value instanceof $class) {
            throw new \RuntimeException(sprintf('Expected %s, got %s.', $class, get_debug_type($value)));
        }

        return $value;
    }

    protected function defaultTranslation(): Translation
    {
        return self::expect(TranslationsStory::get('defaultTranslation'), Translation::class);
    }

    /**
     * Creates a published node-source in the default translation.
     *
     * @template T of NodesSources
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    protected function publishedNode(
        string $nodeTypeName,
        string $class,
        string $title,
        ?Node $parent = null,
        string $publishedAt = 'now',
        NodeStatus $status = NodeStatus::PUBLISHED,
    ): NodesSources {
        $nodeType = $this->nodeTypesBag->get($nodeTypeName)
            ?? throw new \RuntimeException(sprintf('%s node type is missing.', $nodeTypeName));

        $source = self::expect($this->uniqueNodeGenerator->generate(
            nodeType: $nodeType,
            translation: $this->defaultTranslation(),
            parent: $parent,
            flush: false,
        ), $class);
        $source->setTitle($title);
        $source->setPublishedAt(new \DateTime($publishedAt));
        $source->getNode()->setStatus($status);

        return $source;
    }

    protected function relate(NodesSources $from, NodesSources $to, string $fieldName, int $position = 1): void
    {
        $this->entityManager->persist(
            (new NodesToNodes($from->getNode(), $to->getNode()))
                ->setFieldName($fieldName)
                ->setPosition($position)
        );
    }
}
