<?php

declare(strict_types=1);

namespace RZ\Roadiz\Typescript\Declaration\Generators;

use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Typescript\Declaration\DeclarationGeneratorFactory;

final class DeclarationGenerator
{
    /**
     * @var array<NodeTypeInterface>
     */
    private array $nodeTypes;

    /**
     * @param DeclarationGeneratorFactory $generatorFactory
     * @param NodeTypeInterface[] $nodeTypes
     */
    public function __construct(
        private readonly DeclarationGeneratorFactory $generatorFactory,
        array $nodeTypes = []
    ) {
        if (empty($nodeTypes)) {
            $this->nodeTypes = array_unique($this->generatorFactory->getNodeTypesBag()->all());
        } else {
            $this->nodeTypes = $nodeTypes;
        }
    }

    public function getContents(): string
    {
        $blocks = [
            $this->getHeader(),
        ];

        foreach ($this->nodeTypes as $nodeType) {
            $blocks[] = $this->generatorFactory->createForNodeType($nodeType)->getContents();
        }

        $blocks[] = $this->getAllTypesInterface();

        return implode(PHP_EOL . PHP_EOL, $blocks);
    }

    private function getAllTypesInterface(): string
    {
        $nodeTypeNames = array_map(function (NodeTypeInterface $nodeType) {
            return $nodeType->getSourceEntityClassName();
        }, $this->nodeTypes);

        $nodeTypeNames = implode(' | ', $nodeTypeNames);

        return <<<EOT
export type AllRoadizNodesSources = {$nodeTypeNames};
EOT;
    }

    private function getHeader(): string
    {
        return <<<EOT
/*
 * This is an automated Roadiz interface declaration file.
 * RoadizNodesSources, RoadizDocument and other mentioned types are part of
 * roadiz/abstract-api-client package which must be installed in your project.
 *
 * @see https://github.com/roadiz/abstract-api-client
 *
 * Roadiz CMS node-types interfaces
 *
 * @see https://docs.roadiz.io/en/latest/developer/nodes-system/intro.html#what-is-a-node-type
 */

import { RoadizNodesSources, RoadizDocument } from '@roadiz/abstract-api-client/dist/types/roadiz'
EOT;
    }
}
