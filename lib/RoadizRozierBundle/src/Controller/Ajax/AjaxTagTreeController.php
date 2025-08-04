<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\RozierBundle\Widget\TagTreeWidget;
use RZ\Roadiz\RozierBundle\Widget\TreeWidgetFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    public function getTreeAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');
        $translation = $this->getTranslation($request);

        /** @var TagTreeWidget|null $tagTree */
        $tagTree = null;
        $assignation = [];

        switch ($request->get('_action')) {
            /*
             * Inner tag edit for tagTree
             */
            case 'requestTagTree':
                if ($request->get('parentTagId') > 0) {
                    $tag = $this->managerRegistry
                        ->getRepository(Tag::class)
                        ->find((int) $request->get('parentTagId'));
                } else {
                    $tag = null;
                }

                $tagTree = $this->treeWidgetFactory->createTagTree($tag, $translation);

                $assignation['mainTagTree'] = false;

                break;
                /*
                 * Main panel tree tagTree
                 */
            case 'requestMainTagTree':
                $parent = null;
                $tagTree = $this->treeWidgetFactory->createTagTree($parent, $translation);
                $assignation['mainTagTree'] = true;
                break;
        }

        $assignation['tagTree'] = $tagTree;

        return $this->createSerializedResponse([
            'statusCode' => '200',
            'status' => 'success',
            'tagTree' => $this->twig->render('@RoadizRozier/widgets/tagTree/tagTree.html.twig', $assignation),
        ]);
    }
}
