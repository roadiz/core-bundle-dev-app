<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use Doctrine\Persistence\ManagerRegistry;
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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AjaxSearchController extends AbstractAjaxController
{
    public const int RESULT_COUNT = 10;

    public function __construct(
        private readonly Security $security,
        private readonly ExplorerItemFactoryInterface $explorerItemFactory,
        private readonly GlobalNodeSourceSearchHandler $globalNodeSourceSearchHandler,
        ManagerRegistry $managerRegistry,
        SerializerInterface $serializer,
        TranslatorInterface $translator,
    ) {
        parent::__construct($managerRegistry, $serializer, $translator);
    }

    /**
     * Handle AJAX edition requests for Node such as coming from node-tree widgets.
     */
    #[Route('/rz-admin/ajax/search', name: 'searchAjax', methods: ['GET'], format: 'json')]
    public function searchAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(NodeVoter::SEARCH);

        if (!$request->query->has('searchTerms') || '' == $request->query->get('searchTerms')) {
            throw new BadRequestHttpException('searchTerms parameter is missing.');
        }

        $nodesSources = $this->globalNodeSourceSearchHandler->getNodeSourcesBySearchTerm(
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
                $uniqueKey = 'n_'.$source->getNode()->getId();
                if (!$this->security->isGranted(NodeVoter::READ, $source)) {
                    continue;
                }
            } elseif ($source instanceof PersistableInterface) {
                $uniqueKey = 'p_'.$source->getId();
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
