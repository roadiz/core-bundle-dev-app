<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use Doctrine\ORM\NonUniqueResultException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Model\DocumentDto;
use RZ\Roadiz\CoreBundle\Repository\DocumentRepository;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;
use RZ\Roadiz\CoreBundle\Repository\NotPublishedNodesSourcesRepository;
use RZ\Roadiz\CoreBundle\Repository\TagRepository;
use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Extension that allow to gather nodes-source from hierarchy.
 */
final class NodesSourcesExtension extends AbstractExtension
{
    public function __construct(
        private readonly DecoratedNodeTypes $nodeTypesBag,
        private readonly NodesSourcesRepository $nodesSourcesRepository,
        private readonly NotPublishedNodesSourcesRepository $notPublishedNodesSourcesRepository,
        private readonly TagRepository $tagRepository,
        private readonly DocumentRepository $documentRepository,
        private readonly bool $throwExceptions = false,
    ) {
    }

    #[\Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('children', $this->getChildren(...)),
            new TwigFilter('next', $this->getNext(...)),
            new TwigFilter('previous', $this->getPrevious(...)),
            new TwigFilter('lastSibling', $this->getLastSibling(...)),
            new TwigFilter('firstSibling', $this->getFirstSibling(...)),
            new TwigFilter('parent', $this->getParent(...)),
            new TwigFilter('parents', $this->getParents(...)),
            new TwigFilter('tags', $this->getTags(...)),
        ];
    }

    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('first_displayable_image', $this->getFirstDisplayableImage(...)),
        ];
    }

    public function getFirstDisplayableImage(?NodesSources $nodesSources): ?DocumentDto
    {
        return null !== $nodesSources ? $this->documentRepository
            ->findOneDisplayableDtoByNodeSource(
                $nodesSources,
            ) : null;
    }

    #[\Override]
    public function getTests(): array
    {
        $tests = [];

        foreach ($this->nodeTypesBag->all() as $nodeType) {
            $tests[] = new TwigTest($nodeType->getName(), fn ($mixed) => null !== $mixed && $mixed::class === $nodeType->getSourceEntityFullQualifiedClassName());
            $tests[] = new TwigTest($nodeType->getSourceEntityClassName(), fn ($mixed) => null !== $mixed && $mixed::class === $nodeType->getSourceEntityFullQualifiedClassName());
        }

        return $tests;
    }

    /**
     * @return iterable<NodesSources>
     *
     * @throws RuntimeError
     */
    public function getChildren(?NodesSources $ns = null, ?array $criteria = null, ?array $order = null, bool $displayNotPublished = false): iterable
    {
        if (null === $ns) {
            if ($this->throwExceptions) {
                throw new RuntimeError('Cannot get children from a NULL node-source.');
            } else {
                return [];
            }
        }

        if ($displayNotPublished) {
            $repository = $this->notPublishedNodesSourcesRepository;
        } else {
            $repository = $this->nodesSourcesRepository;
        }

        return $repository->findChildren($ns, $criteria, $order);
    }

    /**
     * @throws RuntimeError
     */
    public function getNext(?NodesSources $ns = null, ?array $criteria = null, ?array $order = null, bool $displayNotPublished = false): ?NodesSources
    {
        if (null === $ns) {
            if ($this->throwExceptions) {
                throw new RuntimeError('Cannot get next sibling from a NULL node-source.');
            } else {
                return null;
            }
        }

        if ($displayNotPublished) {
            $repository = $this->notPublishedNodesSourcesRepository;
        } else {
            $repository = $this->nodesSourcesRepository;
        }

        return $repository->findNext($ns, $criteria, $order);
    }

    /**
     * @throws RuntimeError
     */
    public function getPrevious(?NodesSources $ns = null, ?array $criteria = null, ?array $order = null, bool $displayNotPublished = false): ?NodesSources
    {
        if (null === $ns) {
            if ($this->throwExceptions) {
                throw new RuntimeError('Cannot get previous sibling from a NULL node-source.');
            } else {
                return null;
            }
        }

        if ($displayNotPublished) {
            $repository = $this->notPublishedNodesSourcesRepository;
        } else {
            $repository = $this->nodesSourcesRepository;
        }

        return $repository->findPrevious($ns, $criteria, $order);
    }

    /**
     * @throws RuntimeError
     */
    public function getLastSibling(?NodesSources $ns = null, ?array $criteria = null, ?array $order = null, bool $displayNotPublished = false): ?NodesSources
    {
        if (null === $ns) {
            if ($this->throwExceptions) {
                throw new RuntimeError('Cannot get last sibling from a NULL node-source.');
            } else {
                return null;
            }
        }

        if ($displayNotPublished) {
            $repository = $this->notPublishedNodesSourcesRepository;
        } else {
            $repository = $this->nodesSourcesRepository;
        }

        return $repository->findLastSibling($ns, $criteria, $order);
    }

    /**
     * @throws RuntimeError
     */
    public function getFirstSibling(?NodesSources $ns = null, ?array $criteria = null, ?array $order = null, bool $displayNotPublished = false): ?NodesSources
    {
        if (null === $ns) {
            if ($this->throwExceptions) {
                throw new RuntimeError('Cannot get first sibling from a NULL node-source.');
            } else {
                return null;
            }
        }

        if ($displayNotPublished) {
            $repository = $this->notPublishedNodesSourcesRepository;
        } else {
            $repository = $this->nodesSourcesRepository;
        }

        return $repository->findFirstSibling($ns, $criteria, $order);
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
    public function getParents(?NodesSources $ns = null, ?array $criteria = null, bool $displayNotPublished = false): array
    {
        if (null === $ns) {
            if ($this->throwExceptions) {
                throw new RuntimeError('Cannot get parents from a NULL node-source.');
            } else {
                return [];
            }
        }

        if ($displayNotPublished) {
            $repository = $this->notPublishedNodesSourcesRepository;
        } else {
            $repository = $this->nodesSourcesRepository;
        }

        return $repository->findParents($ns, $criteria);
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

        return $this->tagRepository->findByNodesSources($ns);
    }
}
