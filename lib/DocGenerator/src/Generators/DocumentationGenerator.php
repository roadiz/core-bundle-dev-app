<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documentation\Generators;

use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Contracts\Translation\TranslatorInterface;

class DocumentationGenerator
{
    private MarkdownGeneratorFactory $markdownGeneratorFactory;
    private ?array $reachableTypeGenerators = null;
    private ?array $nonReachableTypeGenerators = null;

    public function __construct(
        private readonly ParameterBag $nodeTypesBag,
        private readonly TranslatorInterface $translator,
    ) {
        $this->markdownGeneratorFactory = new MarkdownGeneratorFactory($nodeTypesBag, $translator);
    }

    /**
     * @return array<NodeTypeInterface>
     */
    protected function getAllNodeTypes(): array
    {
        return array_unique($this->nodeTypesBag->all());
    }

    /**
     * @return array<NodeTypeInterface>
     */
    protected function getReachableTypes(): array
    {
        return array_filter($this->getAllNodeTypes(), function (NodeTypeInterface $nodeType) {
            return $nodeType->isReachable();
        });
    }

    /**
     * @return array<NodeTypeInterface>
     */
    protected function getNonReachableTypes(): array
    {
        return array_filter($this->getAllNodeTypes(), function (NodeTypeInterface $nodeType) {
            return !$nodeType->isReachable();
        });
    }

    /**
     * @return NodeTypeGenerator[]
     */
    public function getReachableTypeGenerators(): array
    {
        if (null === $this->reachableTypeGenerators) {
            $this->reachableTypeGenerators = array_map(function (NodeTypeInterface $nodeType) {
                return $this->markdownGeneratorFactory->createForNodeType($nodeType);
            }, $this->getReachableTypes());
        }

        return $this->reachableTypeGenerators;
    }

    /**
     * @return NodeTypeGenerator[]
     */
    public function getNonReachableTypeGenerators(): array
    {
        if (null === $this->nonReachableTypeGenerators) {
            $this->nonReachableTypeGenerators = array_map(function (NodeTypeInterface $nodeType) {
                return $this->markdownGeneratorFactory->createForNodeType($nodeType);
            }, $this->getNonReachableTypes());
        }

        return $this->nonReachableTypeGenerators;
    }

    public function getNavBar(): string
    {
        /*
         * <!-- _navbar.md -->

            * [Introduction](/)
            * Blocs
                * [Groupe de blocs](blocks/groupblock.md)
                * [Bloc de contenu](blocks/contentblock.md)
         */

        $pages = [];
        foreach ($this->getReachableTypeGenerators() as $reachableTypeGenerator) {
            $pages[] = $reachableTypeGenerator->getMenuEntry();
        }

        $blocks = [];
        foreach ($this->getNonReachableTypeGenerators() as $nonReachableTypeGenerator) {
            $blocks[] = $nonReachableTypeGenerator->getMenuEntry();
        }

        return implode("\n", [
            '* '.$this->translator->trans('docs.pages'),
            '    * '.implode("\n    * ", $pages),
            '* '.$this->translator->trans('docs.blocks'),
            '    * '.implode("\n    * ", $blocks),
        ]);
    }
}
