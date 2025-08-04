<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Tag;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
final class TagUtilsController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * Export a Tag in a Json file.
     */
    public function exportAction(Request $request, int $tagId): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        $existingTag = $this->managerRegistry->getRepository(Tag::class)->find($tagId);

        return new JsonResponse(
            $this->serializer->serialize(
                $existingTag,
                'json',
                ['groups' => ['tag', 'tag_base', 'tag_children', 'translated_tag', 'translation_base', 'position']]
            ),
            Response::HTTP_OK,
            [
                'Content-Disposition' => sprintf(
                    'attachment; filename="%s"',
                    'tag-'.$existingTag->getTagName().'-'.date('YmdHis').'.json'
                ),
            ],
            true
        );
    }

    /**
     * Export a Tag in a Json file.
     */
    public function exportAllAction(Request $request, int $tagId): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        $existingTags = $this->managerRegistry
                              ->getRepository(Tag::class)
                              ->findBy(['parent' => null]);

        return new JsonResponse(
            $this->serializer->serialize(
                $existingTags,
                'json',
                ['groups' => ['tag', 'tag_base', 'tag_children', 'translated_tag', 'translation_base', 'position']]
            ),
            Response::HTTP_OK,
            [
                'Content-Disposition' => sprintf(
                    'attachment; filename="%s"',
                    'tag-all-'.date('YmdHis').'.json'
                ),
            ],
            true
        );
    }
}
