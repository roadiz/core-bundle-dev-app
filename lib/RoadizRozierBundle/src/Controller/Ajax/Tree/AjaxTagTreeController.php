<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax\Tree;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\RozierBundle\Controller\Ajax\AbstractAjaxController;
use RZ\Roadiz\RozierBundle\Widget\TagTreeWidget;
use RZ\Roadiz\RozierBundle\Widget\TreeWidgetFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class AjaxTagTreeController extends AbstractAjaxController
{
    public function __construct(
        private readonly TreeWidgetFactory $treeWidgetFactory,
        private readonly Environment $twig,
        ManagerRegistry $managerRegistry,
        SerializerInterface $serializer,
        TranslatorInterface $translator,
    ) {
        parent::__construct($managerRegistry, $serializer, $translator);
    }

    #[Route(
        path: '/rz-admin/ajax/tags/tree',
        name: 'tagsTreeAjax',
        methods: ['GET'],
        format: 'json'
    )]
    public function getTreeAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');
        $translation = $this->getTranslation($request);

        /** @var TagTreeWidget|null $tagTree */
        $tagTree = null;
        $assignation = [];

        switch ($request->query->get('_action')) {
            /*
             * Inner tag edit for tagTree
             */
            case 'requestTagTree':
                if ($request->query->get('parentTagId') > 0) {
                    $tag = $this->managerRegistry
                        ->getRepository(Tag::class)
                        ->find((int) $request->query->get('parentTagId'));
                } else {
                    $tag = null;
                }

                $tagTree = $this->treeWidgetFactory->createTagTree($tag, $translation);

                $assignation['mainTree'] = false;

                break;
                /*
                 * Main panel tree tagTree
                 */
            case 'requestMainTree':
                $parent = null;
                $tagTree = $this->treeWidgetFactory->createTagTree($parent, $translation);
                $assignation['mainTree'] = true;
                break;
        }

        $assignation['tree'] = $tagTree;
        $assignation['tree_type'] = 'tag';

        return $this->createSerializedResponse([
            'statusCode' => '200',
            'status' => 'success',
            'tree_type' => $assignation['tree_type'],
            'tagTree' => $this->twig->render('@RoadizRozier/widgets/tree/rz_tree_wrapper_auto.html.twig', $assignation),
        ]);
    }
}
