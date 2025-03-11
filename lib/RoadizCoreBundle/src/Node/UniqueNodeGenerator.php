<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class UniqueNodeGenerator
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private NodeNamePolicyInterface $nodeNamePolicy,
        private Security $security,
        private NodeTypes $nodeTypesBag,
    ) {
    }

    /**
     * Generate a node with a unique name.
     *
     * This method flush entity-manager by default.
     */
    public function generate(
        NodeType $nodeType,
        TranslationInterface $translation,
        ?Node $parent = null,
        ?Tag $tag = null,
        bool $pushToTop = false,
        bool $flush = true,
    ): NodesSources {
        $name = $nodeType->getDisplayName().' '.uniqid();
        $node = new Node();
        $node->setNodeTypeName($nodeType->getName());
        $node->setTtl($nodeType->getDefaultTtl());

        if (null !== $tag) {
            $node->addTag($tag);
        }
        $parent?->addChild($node);

        if ($pushToTop) {
            /*
             * Force position before first item
             */
            $node->setPosition(0.5);
        }

        /** @var class-string<NodesSources> $sourceClass */ // phpstan hint
        $sourceClass = NodeType::getGeneratedEntitiesNamespace().'\\'.$nodeType->getSourceEntityClassName();

        $source = new $sourceClass($node, $translation);
        $source->setTitle($name);
        $source->setPublishedAt(new \DateTime());
        $node->setNodeName($this->nodeNamePolicy->getCanonicalNodeName($source));

        $manager = $this->managerRegistry->getManagerForClass(Node::class);
        if (null !== $manager) {
            $manager->persist($node);
            $manager->persist($source);
            if ($flush) {
                $manager->flush();
            }
        }

        return $source;
    }

    /**
     * Try to generate a unique node from request variables.
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function generateFromRequest(Request $request): NodesSources
    {
        $pushToTop = false;

        if (1 == $request->get('pushTop')) {
            $pushToTop = true;
        }

        if ($request->get('tagId') > 0) {
            $tag = $this->managerRegistry
                ->getRepository(Tag::class)
                ->find((int) $request->get('tagId'));
        } else {
            $tag = null;
        }

        if ($request->get('parentNodeId') > 0) {
            $parent = $this->managerRegistry
                ->getRepository(Node::class)
                ->find((int) $request->get('parentNodeId'));
            if (null === $parent || !$this->security->isGranted(NodeVoter::CREATE, $parent)) {
                throw new BadRequestHttpException('Parent node does not exist.');
            }
        } else {
            if (!$this->security->isGranted(NodeVoter::CREATE_AT_ROOT)) {
                throw new AccessDeniedException('You are not allowed to create a node at root.');
            }
            $parent = null;
        }

        $nodeType = null;

        $nodeTypeName = $request->get('nodeTypeName');
        if (is_string($nodeTypeName) && !empty($nodeTypeName)) {
            $nodeType = $this->nodeTypesBag->get($nodeTypeName);
        }

        if (null === $nodeType) {
            throw new BadRequestHttpException('Node-type does not exist.');
        }

        if ($request->get('translationId') > 0) {
            /** @var Translation $translation */
            $translation = $this->managerRegistry
                ->getRepository(Translation::class)
                ->find((int) $request->get('translationId'));
        } elseif (null !== $parent && false !== $parentNodeSource = $parent->getNodeSources()->first()) {
            /*
             * If parent has only on translation, use parent translation instead of default one.
             */
            $translation = $parentNodeSource->getTranslation();
        } else {
            /** @var Translation $translation */
            $translation = $this->managerRegistry
                ->getRepository(Translation::class)
                ->findDefault();
        }

        return $this->generate(
            $nodeType,
            $translation,
            $parent,
            $tag,
            $pushToTop
        );
    }
}
