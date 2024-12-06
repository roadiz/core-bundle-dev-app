<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;

interface NodeNamePolicyInterface
{
    /**
     * @return string return a canonical node name built against a NS title and node-type
     */
    public function getCanonicalNodeName(NodesSources $nodeSource): string;

    /**
     * @return string return a canonical node' name built against a NS title, node-type and a unique suffix
     */
    public function getSafeNodeName(NodesSources $nodeSource): string;

    /**
     * @return string return a canonical node' name built against a NS title, node-type and a date suffix
     */
    public function getDatestampedNodeName(NodesSources $nodeSource): string;

    public function isNodeNameWithUniqId(string $canonicalNodeName, string $nodeName): bool;

    public function isNodeNameAlreadyUsed(string $nodeName): bool;

    public function isNodeNameValid(string $nodeName): bool;
}
