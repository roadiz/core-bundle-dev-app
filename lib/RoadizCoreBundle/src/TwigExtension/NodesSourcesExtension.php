<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigTest;

/**
 * Extension that allow to gather nodes-source from hierarchy.
 */
final class NodesSourcesExtension extends AbstractExtension
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly NodeTypes $nodeTypesBag,
        private readonly bool $throwExceptions = false,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('children', [$this, 'getChildren']),
            new TwigFilter('next', [$this, 'getNext']),
            new TwigFilter('previous', [$this, 'getPrevious']),
            new TwigFilter('lastSibling', [$this, 'getLastSibling']),
            new TwigFilter('firstSibling', [$this, 'getFirstSibling']),
            new TwigFilter('parent', [$this, 'getParent']),
            new TwigFilter('parents', [$this, 'getParents']),
            new TwigFilter('tags', [$this, 'getTags']),
        ];
    }

    public function getTests(): array
    {
        $tests = [];

        foreach ($this->nodeTypesBag->all() as $nodeType) {
            $tests[] = new TwigTest($nodeType->getName(), function ($mixed) use ($nodeType) {
                return null !== $mixed && get_class($mixed) === $nodeType->getSourceEntityFullQualifiedClassName();
            });
            $tests[] = new TwigTest($nodeType->getSourceEntityClassName(), function ($mixed) use ($nodeType) {
                return null !== $mixed && get_class($mixed) === $nodeType->getSourceEntityFullQualifiedClassName();
            });
        }

        return $tests;
    }

    /**
     * @return iterable<NodesSources>
     *
     * @throws RuntimeError
     */
    public function getChildren(?NodesSources $ns = null, ?array $criteria = null, ?array $order = null): iterable
    {
        if (null === $ns) {
            if ($this->throwExceptions) {
                throw new RuntimeError('Cannot get children from a NULL node-source.');
            } else {
                return [];
            }
        }

        return $this->managerRegistry
            ->getRepository(NodesSources::class)
            ->findChildren($ns, $criteria, $order);
    }

    /**
     * @throws RuntimeError
     */
    public function getNext(?NodesSources $ns = null, ?array $criteria = null, ?array $order = null): ?NodesSources
    {
        if (null === $ns) {
            if ($this->throwExceptions) {
                throw new RuntimeError('Cannot get next sibling from a NULL node-source.');
            } else {
                return null;
            }
        }

        return $this->managerRegistry
            ->getRepository(NodesSources::class)
            ->findNext($ns, $criteria, $order);
    }

    /**
     * @throws RuntimeError
     */
    public function getPrevious(?NodesSources $ns = null, ?array $criteria = null, ?array $order = null): ?NodesSources
    {
        if (null === $ns) {
            if ($this->throwExceptions) {
                throw new RuntimeError('Cannot get previous sibling from a NULL node-source.');
            } else {
                return null;
            }
        }

        return $this->managerRegistry
            ->getRepository(NodesSources::class)
            ->findPrevious($ns, $criteria, $order);
    }

    /**
     * @throws RuntimeError
     */
    public function getLastSibling(?NodesSources $ns = null, ?array $criteria = null, ?array $order = null): ?NodesSources
    {
        if (null === $ns) {
            if ($this->throwExceptions) {
                throw new RuntimeError('Cannot get last sibling from a NULL node-source.');
            } else {
                return null;
            }
        }

        return $this->managerRegistry
            ->getRepository(NodesSources::class)
            ->findLastSibling($ns, $criteria, $order);
    }

    /**
     * @throws RuntimeError
     */
    public function getFirstSibling(?NodesSources $ns = null, ?array $criteria = null, ?array $order = null): ?NodesSources
    {
        if (null === $ns) {
            if ($this->throwExceptions) {
                throw new RuntimeError('Cannot get first sibling from a NULL node-source.');
            } else {
                return null;
            }
        }

        return $this->managerRegistry
            ->getRepository(NodesSources::class)
            ->findFirstSibling($ns, $criteria, $order);
    }

    /**
     * @throws RuntimeError
     */
    public function getParent(?NodesSources $ns = null): ?NodesSources
    {
        if (null === $ns) {
            if ($this->throwExceptions) {
                throw new RuntimeError('Cannot get parent from a NULL node-source.');
            } else {
                return null;
            }
        }

        return $ns->getParent();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws RuntimeError
     * @throws NonUniqueResultException
     */
    public function getParents(?NodesSources $ns = null, ?array $criteria = null): array
    {
        if (null === $ns) {
            if ($this->throwExceptions) {
                throw new RuntimeError('Cannot get parents from a NULL node-source.');
            } else {
                return [];
            }
        }

        return $this->managerRegistry
            ->getRepository(NodesSources::class)
            ->findParents($ns, $criteria);
    }

    /**
     * @return iterable<Tag>
     *
     * @throws RuntimeError
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getTags(?NodesSources $ns = null): iterable
    {
        if (null === $ns) {
            if ($this->throwExceptions) {
                throw new RuntimeError('Cannot get tags from a NULL node-source.');
            } else {
                return [];
            }
        }

        return $this->managerRegistry
            ->getRepository(Tag::class)
            ->findByNodesSources($ns);
    }
}
