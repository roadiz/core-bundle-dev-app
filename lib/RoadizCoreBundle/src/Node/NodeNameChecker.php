<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\UrlAlias;
use RZ\Roadiz\CoreBundle\Repository\NodeRepository;
use RZ\Roadiz\CoreBundle\Repository\UrlAliasRepository;
use RZ\Roadiz\Utils\StringHandler;

final readonly class NodeNameChecker implements NodeNamePolicyInterface
{
    public const int MAX_LENGTH = 250;

    public function __construct(
        private ManagerRegistry $managerRegistry,
        private bool $useTypedSuffix = false,
    ) {
    }

    #[\Override]
    public function getCanonicalNodeName(NodesSources $nodeSource): string
    {
        $nodeTypeSuffix = StringHandler::slugify($nodeSource->getNodeTypeName());
        if ('' !== $nodeSource->getTitle()) {
            $title = StringHandler::slugify($nodeSource->getTitle());
            if ($nodeSource->isReachable() || !$this->useTypedSuffix) {
                // truncate title to 250 chars if needed
                if (\mb_strlen($title) > self::MAX_LENGTH) {
                    $title = \mb_substr($title, 0, self::MAX_LENGTH);
                }

                return $title;
            }
            // truncate title if title + suffix + 1 exceed 250 chars
            if ((\mb_strlen($title) + \mb_strlen($nodeTypeSuffix) + 1) > self::MAX_LENGTH) {
                $title = \mb_substr($title, 0, self::MAX_LENGTH - (\mb_strlen($nodeTypeSuffix) + 1));
            }

            return sprintf(
                '%s-%s',
                $title,
                $nodeTypeSuffix,
            );
        }

        return sprintf(
            '%s-%s',
            $nodeTypeSuffix,
            null !== $nodeSource->getNode()->getId()
        );
    }

    #[\Override]
    public function getSafeNodeName(NodesSources $nodeSource): string
    {
        $canonicalNodeName = $this->getCanonicalNodeName($nodeSource);
        $uniqueId = uniqid();

        // truncate canonicalNodeName if canonicalNodeName + uniqueId + 1 exceed 250 chars
        if ((\mb_strlen($canonicalNodeName) + \mb_strlen($uniqueId) + 1) > self::MAX_LENGTH) {
            $canonicalNodeName = \mb_substr(
                $canonicalNodeName,
                0,
                self::MAX_LENGTH - (\mb_strlen($uniqueId) + 1)
            );
        }

        return sprintf(
            '%s-%s',
            $canonicalNodeName,
            $uniqueId
        );
    }

    #[\Override]
    public function getDatestampedNodeName(NodesSources $nodeSource): string
    {
        $canonicalNodeName = $this->getCanonicalNodeName($nodeSource);
        $timestamp = $nodeSource->getPublishedAt()->format('Y-m-d');

        // truncate canonicalNodeName if canonicalNodeName + uniqueId + 1 exceed 250 chars
        if ((\mb_strlen($canonicalNodeName) + \mb_strlen($timestamp) + 1) > self::MAX_LENGTH) {
            $canonicalNodeName = \mb_substr(
                $canonicalNodeName,
                0,
                self::MAX_LENGTH - (\mb_strlen($timestamp) + 1)
            );
        }

        return sprintf(
            '%s-%s',
            $canonicalNodeName,
            $timestamp
        );
    }

    /**
     * Test if current node name is suffixed with a 13 chars Unique ID (uniqid()).
     *
     * @param string $canonicalNodeName node name without uniqid after
     * @param string $nodeName          Node name to test
     */
    #[\Override]
    public function isNodeNameWithUniqId(string $canonicalNodeName, string $nodeName): bool
    {
        $pattern = '#^'.preg_quote($canonicalNodeName).'\-[0-9a-z]{13}$#';
        $returnState = preg_match_all($pattern, $nodeName);

        if (1 === $returnState) {
            return true;
        }

        return false;
    }

    #[\Override]
    public function isNodeNameValid(string $nodeName): bool
    {
        if (1 === preg_match('#^[a-zA-Z0-9\-]+$#', $nodeName)) {
            return true;
        }

        return false;
    }

    /**
     * Test if node’s name is already used as a name or an url-alias.
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    #[\Override]
    public function isNodeNameAlreadyUsed(string $nodeName): bool
    {
        $nodeName = StringHandler::slugify($nodeName);
        /** @var UrlAliasRepository $urlAliasRepo */
        $urlAliasRepo = $this->managerRegistry->getRepository(UrlAlias::class);
        /** @var NodeRepository $nodeRepo */
        $nodeRepo = $this->managerRegistry
            ->getRepository(Node::class)
            ->setDisplayingNotPublishedNodes(true);

        if (
            false === $urlAliasRepo->exists($nodeName)
            && false === $nodeRepo->exists($nodeName)
        ) {
            return false;
        }

        return true;
    }
}
