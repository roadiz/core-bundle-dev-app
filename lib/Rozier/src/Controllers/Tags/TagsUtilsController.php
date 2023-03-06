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

/**
 * @package Themes\Rozier\Controllers\Tags
 */
class TagsUtilsController extends RozierApp
{
    private SerializerInterface $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Export a Tag in a Json file
     *
     * @param Request $request
     * @param int     $tagId
     *
     * @return Response
     */
    public function exportAction(Request $request, int $tagId)
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        $existingTag = $this->em()->find(Tag::class, $tagId);

        return new JsonResponse(
            $this->serializer->serialize(
                $existingTag,
                'json',
                SerializationContext::create()->setGroups(['tag', 'position'])
            ),
            JsonResponse::HTTP_OK,
            [
                'Content-Disposition' => sprintf(
                    'attachment; filename="%s"',
                    'tag-' . $existingTag->getTagName() . '-' . date("YmdHis")  . '.json'
                ),
            ],
            true
        );
    }

    /**
     * Export a Tag in a Json file
     *
     * @param Request $request
     * @param int $tagId
     *
     * @return Response
     */
    public function exportAllAction(Request $request, int $tagId)
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        $existingTags = $this->em()
                              ->getRepository(Tag::class)
                              ->findBy(["parent" => null]);

        return new JsonResponse(
            $this->serializer->serialize(
                $existingTags,
                'json',
                SerializationContext::create()->setGroups(['tag', 'position'])
            ),
            JsonResponse::HTTP_OK,
            [
                'Content-Disposition' => sprintf(
                    'attachment; filename="%s"',
                    'tag-all-' . date("YmdHis") . '.json'
                ),
            ],
            true
        );
    }
}
