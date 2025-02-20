<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Themes\Rozier\Widgets\NodeTreeWidget;
use Themes\Rozier\Widgets\TreeWidgetFactory;

final class AjaxNodeTreeController extends AbstractAjaxController
{
    public function __construct(
        private readonly NodeChrootResolver $nodeChrootResolver,
        private readonly TreeWidgetFactory $treeWidgetFactory,
        private readonly NodeTypes $nodeTypesBag,
        SerializerInterface $serializer,
    ) {
        parent::__construct($serializer);
    }

    public function getTreeAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');
        $translation = $this->getTranslation($request);

        /** @var NodeTreeWidget|null $nodeTree */
        $nodeTree = null;
        /** @var NodeType[] $linkedTypes */
        $linkedTypes = [];

        switch ($request->get('_action')) {
            /*
             * Inner node edit for nodeTree
             */
            case 'requestNodeTree':
                if ($request->get('parentNodeId') > 0) {
                    $node = $this->em()
                                ->find(
                                    Node::class,
                                    (int) $request->get('parentNodeId')
                                );
                } elseif (null !== $this->getUser()) {
                    $node = $this->nodeChrootResolver->getChroot($this->getUser());
                } else {
                    $node = null;
                }

                $nodeTree = $this->treeWidgetFactory->createNodeTree($node, $translation);

                if (
                    $request->get('tagId')
                    && $request->get('tagId') > 0
                ) {
                    $filterTag = $this->em()
                                        ->find(
                                            Tag::class,
                                            (int) $request->get('tagId')
                                        );

                    $nodeTree->setTag($filterTag);
                }

                /*
                 * Filter view with only listed node-types
                 */
                $linkedTypesNames = $request->get('linkedTypes', []);
                if (is_array($linkedTypesNames) && count($linkedTypesNames) > 0) {
                    /*
                     * Linked types must be NodeType entities, not only string
                     * to expose name and displayName to ajax responses.
                     */
                    $linkedTypes = array_filter(array_map(function (string $typeName) {
                        return $this->nodeTypesBag->get($typeName);
                    }, $linkedTypesNames));

                    $nodeTree->setAdditionalCriteria([
                        'nodeTypeName' => $linkedTypesNames,
                    ]);
                }

                $this->assignation['mainNodeTree'] = false;

                if (true === (bool) $request->get('stackTree')) {
                    $nodeTree->setStackTree(true);
                }
                break;
                /*
                 * Main panel tree nodeTree
                 */
            case 'requestMainNodeTree':
                $parent = null;
                if (null !== $this->getUser()) {
                    $parent = $this->nodeChrootResolver->getChroot($this->getUser());
                }

                $nodeTree = $this->treeWidgetFactory->createRootNodeTree($parent, $translation);
                $this->assignation['mainNodeTree'] = true;
                break;
        }

        $this->assignation['nodeTree'] = $nodeTree;
        // Need to expose linkedTypes to add data-attributes on widget again
        $this->assignation['linkedTypes'] = $linkedTypes;

        return $this->createSerializedResponse([
            'statusCode' => '200',
            'status' => 'success',
            'linkedTypes' => $linkedTypes,
            'nodeTree' => trim($this->getTwig()->render('@RoadizRozier/widgets/nodeTree/nodeTree.html.twig', $this->assignation)),
        ]);
    }
}
