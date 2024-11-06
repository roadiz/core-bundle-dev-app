<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Tags;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\RozierApp;

class TagsUtilsController extends RozierApp
{
    public function __construct(private readonly SerializerInterface $serializer)
    {
    }

    /**
     * Export a Tag in a Json file.
     */
    public function exportAction(Request $request, int $tagId): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        $existingTag = $this->em()->find(Tag::class, $tagId);

        return new JsonResponse(
            $this->serializer->serialize(
                $existingTag,
                'json',
                SerializationContext::create()->setGroups(['tag', 'position'])
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

        $existingTags = $this->em()
                              ->getRepository(Tag::class)
                              ->findBy(['parent' => null]);

        return new JsonResponse(
            $this->serializer->serialize(
                $existingTags,
                'json',
                SerializationContext::create()->setGroups(['tag', 'position'])
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
