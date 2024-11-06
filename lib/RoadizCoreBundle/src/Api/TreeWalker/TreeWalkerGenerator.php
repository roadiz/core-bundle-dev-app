<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker;

use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\Definition\DefinitionFactoryConfiguration;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\Definition\DefinitionFactoryInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\EntityApi\NodeSourceApi;
use RZ\TreeWalker\AbstractWalker;
use RZ\TreeWalker\WalkerContextInterface;
use RZ\TreeWalker\WalkerInterface;
use Symfony\Component\String\UnicodeString;

final class TreeWalkerGenerator
{
    /**
     * @var array<class-string, DefinitionFactoryConfiguration>
     */
    private array $walkerDefinitionFactories = [];

    public function __construct(
        private readonly NodeSourceApi $nodeSourceApi,
        private readonly NodeTypes $nodeTypesBag,
        private readonly WalkerContextInterface $walkerContext,
        private readonly CacheItemPoolInterface $cacheItemPool,
    ) {
    }

    /**
     * @param class-string<AbstractWalker> $walkerClass
     *
     * @return array<string, WalkerInterface>
     */
    public function getTreeWalkersForTypeAtRoot(
        string $nodeType,
        string $walkerClass,
        TranslationInterface $translation,
        int $maxLevel = 3,
    ): array {
        $walkers = [];
        /** @var NodesSources[] $roots */
        $roots = $this->nodeSourceApi->getBy([
            'node.nodeType' => $this->nodeTypesBag->get($nodeType),
            'node.parent' => null,
            'translation' => $translation,
        ]);

        foreach ($roots as $root) {
            $walkerName = (new UnicodeString($root->getNode()->getNodeName().' walker'))
                ->trim()
                ->camel()
                ->toString();

            $walkers[$walkerName] = $this->buildForRoot(
                $root,
                $walkerClass,
                $this->walkerContext,
                $maxLevel,
                $this->cacheItemPool
            );
        }

        return $walkers;
    }

    /**
     * @param class-string<AbstractWalker> $walkerClass
     */
    public function buildForRoot(
        object $root,
        string $walkerClass,
        WalkerContextInterface $walkerContext,
        int $maxLevel,
        CacheItemPoolInterface $cacheItemPool,
    ): WalkerInterface {
        /** @var callable $callable */
        $callable = [$walkerClass, 'build'];
        $walker = call_user_func(
            $callable,
            $root,
            $walkerContext,
            $maxLevel,
            $cacheItemPool
        );

        foreach ($this->walkerDefinitionFactories as $definitionFactoryConfiguration) {
            $walker->addDefinition(
                $definitionFactoryConfiguration->classname,
                $definitionFactoryConfiguration->definitionFactory->create(
                    $this->walkerContext,
                    $definitionFactoryConfiguration->onlyVisible
                )
            );
        }

        return $walker;
    }

    /**
     * Inject definition from factories registered in the container
     * using `roadiz_core.tree_walker_definition_factory` tag.
     *
     * @param class-string $classname
     */
    public function addDefinitionFactoryConfiguration(
        string $classname,
        DefinitionFactoryInterface $definitionFactory,
        bool $onlyVisible,
    ): void {
        $this->walkerDefinitionFactories[$classname] = new DefinitionFactoryConfiguration(
            $classname,
            $definitionFactory,
            $onlyVisible
        );
    }
}
