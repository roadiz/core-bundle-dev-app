<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use RZ\Roadiz\CoreBundle\Entity\Tag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Themes\Rozier\Widgets\TagTreeWidget;
use Themes\Rozier\Widgets\TreeWidgetFactory;

final class AjaxTagTreeController extends AbstractAjaxController
{
    public function __construct(
        private readonly TreeWidgetFactory $treeWidgetFactory,
        SerializerInterface $serializer,
    ) {
        parent::__construct($serializer);
    }

    public function getTreeAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');
        $translation = $this->getTranslation($request);

        /** @var TagTreeWidget|null $tagTree */
        $tagTree = null;

        switch ($request->get('_action')) {
            /*
             * Inner tag edit for tagTree
             */
            case 'requestTagTree':
                if ($request->get('parentTagId') > 0) {
                    $tag = $this->em()
                                ->find(
                                    Tag::class,
                                    (int) $request->get('parentTagId')
                                );
                } else {
                    $tag = null;
                }

                $tagTree = $this->treeWidgetFactory->createTagTree($tag, $translation);

                $this->assignation['mainTagTree'] = false;

                break;
                /*
                 * Main panel tree tagTree
                 */
            case 'requestMainTagTree':
                $parent = null;
                $tagTree = $this->treeWidgetFactory->createTagTree($parent, $translation);
                $this->assignation['mainTagTree'] = true;
                break;
        }

        $this->assignation['tagTree'] = $tagTree;

        return $this->createSerializedResponse([
            'statusCode' => '200',
            'status' => 'success',
            'tagTree' => $this->getTwig()->render('@RoadizRozier/widgets/tagTree/tagTree.html.twig', $this->assignation),
        ]);
    }
}
