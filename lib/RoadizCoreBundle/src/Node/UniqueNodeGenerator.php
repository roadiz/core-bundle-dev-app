<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Model\NodeCreationDto;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodeRepository;
use RZ\Roadiz\CoreBundle\Repository\TagRepository;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
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
        private AllStatusesNodeRepository $allStatusesNodeRepository,
        private TranslationRepository $translationRepository,
        private TagRepository $tagRepository,
        private Security $security,
        private NodeTypes $nodeTypesBag,
        private NodeTypeClassLocatorInterface $nodeTypeClassLocator,
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
        $sourceClass = $this->nodeTypeClassLocator->getSourceEntityFullQualifiedClassName($nodeType);

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
     *
     * @deprecated Use generateFromDto() method instead
     */
    public function generateFromRequest(Request $request): NodesSources
    {
        $nodeCreationDto = new NodeCreationDto(
            csrfToken: $request->get('csrfToken', ''),
            nodeTypeName: (string) $request->get('nodeTypeName', ''),
            parentNodeId: $request->get('parentNodeId') > 0 ? (int) $request->get('parentNodeId') : null,
            translationId: $request->get('translationId') > 0 ? (int) $request->get('translationId') : 0,
            tagId: $request->get('tagId') > 0 ? (int) $request->get('tagId') : null,
            pushTop: 1 == $request->get('pushTop'),
        );

        return $this->generateFromDto($nodeCreationDto);
    }

    public function generateFromDto(NodeCreationDto $nodeCreationDto): NodesSources
    {
        if ($nodeCreationDto->tagId > 0) {
            $tag = $this->tagRepository->find($nodeCreationDto->tagId);
        } else {
            $tag = null;
        }

        if ($nodeCreationDto->parentNodeId > 0) {
            $parent = $this->allStatusesNodeRepository->find($nodeCreationDto->parentNodeId);
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

        $nodeTypeName = $nodeCreationDto->nodeTypeName;
        if (is_string($nodeTypeName) && !empty($nodeTypeName)) {
            $nodeType = $this->nodeTypesBag->get($nodeTypeName);
        }

        if (null === $nodeType) {
            throw new BadRequestHttpException('Node-type does not exist.');
        }

        if ($nodeCreationDto->translationId > 0) {
            $translation = $this->translationRepository->find($nodeCreationDto->translationId);
        } elseif (null !== $parent && false !== $parentNodeSource = $parent->getNodeSources()->first()) {
            /*
             * If parent has only on translation, use parent translation instead of default one.
             */
            $translation = $parentNodeSource->getTranslation();
        } else {
            $translation = $this->translationRepository->findDefault();
        }

        if (null === $translation) {
            throw new BadRequestHttpException('Translation does not exist.');
        }

        return $this->generate(
            $nodeType,
            $translation,
            $parent,
            $tag,
            $nodeCreationDto->pushTop
        );
    }
}
