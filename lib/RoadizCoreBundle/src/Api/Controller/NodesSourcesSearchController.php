<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Controller;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Api\ListManager\InvalidSearchQueryException;
use RZ\Roadiz\CoreBundle\Api\ListManager\SolrPaginator;
use RZ\Roadiz\CoreBundle\Api\ListManager\SolrSearchListManager;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\NodeSourceSearchHandlerInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\SearchHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class NodesSourcesSearchController extends AbstractController
{
    use TranslationAwareControllerTrait;

    private ManagerRegistry $managerRegistry;
    private PreviewResolverInterface $previewResolver;
    private ?NodeSourceSearchHandlerInterface $nodeSourceSearchHandler;
    private int $highlightingFragmentSize;

    public function __construct(
        ManagerRegistry $managerRegistry,
        PreviewResolverInterface $previewResolver,
        ?NodeSourceSearchHandlerInterface $nodeSourceSearchHandler,
        int $highlightingFragmentSize = 200,
    ) {
        $this->nodeSourceSearchHandler = $nodeSourceSearchHandler;
        $this->highlightingFragmentSize = $highlightingFragmentSize;
        $this->managerRegistry = $managerRegistry;
        $this->previewResolver = $previewResolver;
    }

    protected function getManagerRegistry(): ManagerRegistry
    {
        return $this->managerRegistry;
    }

    protected function getPreviewResolver(): PreviewResolverInterface
    {
        return $this->previewResolver;
    }

    protected function getSearchHandler(): SearchHandlerInterface
    {
        if (null === $this->nodeSourceSearchHandler) {
            throw new HttpException(Response::HTTP_SERVICE_UNAVAILABLE, 'Search engine does not respond.');
        }
        $this->nodeSourceSearchHandler->boostByPublicationDate();
        if ($this->highlightingFragmentSize > 0) {
            $this->nodeSourceSearchHandler->setHighlightingFragmentSize($this->highlightingFragmentSize);
        }

        return $this->nodeSourceSearchHandler;
    }

    protected function getCriteria(Request $request): array
    {
        return [
            'publishedAt' => ['<=', new \DateTime()],
            'translation' => $this->getTranslation($request),
        ];
    }

    public function __invoke(Request $request): SolrPaginator
    {
        $entityListManager = new SolrSearchListManager(
            $request,
            $this->getSearchHandler(),
            $this->getCriteria($request),
            true
        );

        return new SolrPaginator($entityListManager);
    }
}
