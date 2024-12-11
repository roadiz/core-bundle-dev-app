<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemFactoryInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\GlobalNodeSourceSearchHandler;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;

final class AjaxSearchController extends AbstractAjaxController
{
    public const RESULT_COUNT = 10;

    public function __construct(
        private readonly Security $security,
        private readonly ExplorerItemFactoryInterface $explorerItemFactory,
        SerializerInterface $serializer,
    ) {
        parent::__construct($serializer);
    }

    /**
     * Handle AJAX edition requests for Node
     * such as coming from node-tree widgets.
     *
     * @return Response JSON response
     */
    public function searchAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(NodeVoter::SEARCH);

        if (!$request->query->has('searchTerms') || '' == $request->query->get('searchTerms')) {
            throw new BadRequestHttpException('searchTerms parameter is missing.');
        }

        $searchHandler = new GlobalNodeSourceSearchHandler($this->em());
        $searchHandler->setDisplayNonPublishedNodes(true);

        /** @var array $nodesSources */
        $nodesSources = $searchHandler->getNodeSourcesBySearchTerm(
            $request->get('searchTerms'),
            self::RESULT_COUNT
        );

        if (0 === count($nodesSources)) {
            return new JsonResponse([
                'statusCode' => Response::HTTP_OK,
                'status' => 'success',
                'data' => [],
                'responseText' => 'No results found.',
            ]);
        }

        $data = [];

        foreach ($nodesSources as $source) {
            $uniqueKey = null;
            if ($source instanceof NodesSources) {
                $uniqueKey = 'n_' . $source->getNode()->getId();
                if (!$this->security->isGranted(NodeVoter::READ, $source)) {
                    continue;
                }
            } elseif ($source instanceof PersistableInterface) {
                $uniqueKey = 'p_' . $source->getId();
            }
            if (key_exists($uniqueKey, $data)) {
                continue;
            }

            $data[$uniqueKey] = $this->explorerItemFactory->createForEntity($source)->toArray();
        }

        $data = array_values($data);

        return $this->createSerializedResponse([
            'status' => 'confirm',
            'statusCode' => 200,
            'data' => $data,
            'count' => count($data),
        ]);
    }
}
