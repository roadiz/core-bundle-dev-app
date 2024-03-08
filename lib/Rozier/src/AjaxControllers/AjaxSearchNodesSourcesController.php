<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\SearchEngine\GlobalNodeSourceSearchHandler;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @package Themes\Rozier\AjaxControllers
 */
class AjaxSearchNodesSourcesController extends AbstractAjaxController
{
    public const RESULT_COUNT = 8;
    private DocumentUrlGeneratorInterface $documentUrlGenerator;

    public function __construct(DocumentUrlGeneratorInterface $documentUrlGenerator)
    {
        $this->documentUrlGenerator = $documentUrlGenerator;
    }

    /**
     * Handle AJAX edition requests for Node
     * such as coming from nodetree widgets.
     *
     * @param Request $request
     *
     * @return Response JSON response
     */
    public function searchAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');

        if (!$request->query->has('searchTerms') || $request->query->get('searchTerms') == '') {
            throw new BadRequestHttpException('searchTerms parameter is missing.');
        }

        $searchHandler = new GlobalNodeSourceSearchHandler($this->em());
        $searchHandler->setDisplayNonPublishedNodes(true);

        /** @var array<mixed> $nodesSources */
        $nodesSources = $searchHandler->getNodeSourcesBySearchTerm(
            $request->get('searchTerms'),
            static::RESULT_COUNT
        );

        if (null !== $nodesSources && count($nodesSources) > 0) {
            $responseArray = [
                'statusCode' => Response::HTTP_OK,
                'status' => 'success',
                'data' => [],
                'responseText' => count($nodesSources) . ' results found.',
            ];

            foreach ($nodesSources as $source) {
                if (
                    $source instanceof NodesSources &&
                    !key_exists($source->getNode()->getId(), $responseArray['data'])
                ) {
                    $responseArray['data'][$source->getNode()->getId()] = $this->getNodeSourceData($source);
                }
            }
            /*
             * Only display one nodeSource
             */
            $responseArray['data'] = array_values($responseArray['data']);

            return new JsonResponse(
                $responseArray
            );
        }

        return new JsonResponse([
            'statusCode' => Response::HTTP_OK,
            'status' => 'success',
            'data' => [],
            'responseText' => 'No results found.',
        ]);
    }

    protected function getNodeSourceData(NodesSources $source): array
    {
        $thumbnail = null;
        /** @var Translation $translation */
        $translation = $source->getTranslation();
        $displayableNSDoc = $source->getDocumentsByFields()->filter(function (NodesSourcesDocuments $nsDoc) {
            $doc = $nsDoc->getDocument();
            return !$doc->isPrivate() && ($doc->isImage() || $doc->isSvg());
        })->first();
        if (false !== $displayableNSDoc) {
            $thumbnail = $displayableNSDoc->getDocument();
            $this->documentUrlGenerator->setDocument($thumbnail);
            $this->documentUrlGenerator->setOptions([
                "fit" => "60x60",
                "quality" => 80
            ]);
        }
        return [
            'title' => $source->getTitle() ?? $source->getNode()->getNodeName(),
            'parent' => $source->getParent() ?
                $source->getParent()->getTitle() ?? $source->getParent()->getNode()->getNodeName() :
                null,
            'thumbnail' => $thumbnail ? $this->documentUrlGenerator->getUrl() : null,
            'nodeId' => $source->getNode()->getId(),
            'translationId' => $translation->getId(),
            'typeName' => $source->getNode()->getNodeType()->getLabel(),
            'typeColor' => $source->getNode()->getNodeType()->getColor(),
            'url' => $this->generateUrl(
                'nodesEditSourcePage',
                [
                    'nodeId' => $source->getNode()->getId(),
                    'translationId' => $translation->getId(),
                ]
            ),
        ];
    }
}
