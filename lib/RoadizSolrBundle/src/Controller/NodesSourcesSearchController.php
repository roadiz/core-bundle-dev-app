<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Api\Controller\TranslationAwareControllerTrait;
use RZ\Roadiz\CoreBundle\Api\ListManager\SearchEngineListManager;
use RZ\Roadiz\CoreBundle\Api\ListManager\SearchEnginePaginator;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\NodeSourceSearchHandlerInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\SearchHandlerInterface;
use RZ\Roadiz\SolrBundle\AbstractSearchHandler;
use RZ\Roadiz\SolrBundle\Exception\SolrServerNotAvailableException;
use RZ\Roadiz\SolrBundle\Exception\SolrServerNotConfiguredException;
use RZ\Roadiz\SolrBundle\SolrHighlightingBsTypeEnum;
use RZ\Roadiz\SolrBundle\SolrHighlightingMethodEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

#[AsController]
class NodesSourcesSearchController extends AbstractController
{
    use TranslationAwareControllerTrait;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly PreviewResolverInterface $previewResolver,
        private readonly NodeSourceSearchHandlerInterface $nodeSourceSearchHandler,
        private readonly int $highlightingFragmentSize = 200,
        private readonly SolrHighlightingBsTypeEnum $highlightingBsType = SolrHighlightingBsTypeEnum::WORD,
        private readonly SolrHighlightingMethodEnum $highlightingMethod = SolrHighlightingMethodEnum::UNIFIED,
    ) {
    }

    #[\Override]
    protected function getManagerRegistry(): ManagerRegistry
    {
        return $this->managerRegistry;
    }

    #[\Override]
    protected function getPreviewResolver(): PreviewResolverInterface
    {
        return $this->previewResolver;
    }

    protected function getSearchHandler(): SearchHandlerInterface
    {
        $this->nodeSourceSearchHandler->boostByPublicationDate();
        if ($this->highlightingFragmentSize > 0) {
            $this->nodeSourceSearchHandler->setHighlightingFragmentSize($this->highlightingFragmentSize);
        }

        if ($this->nodeSourceSearchHandler instanceof AbstractSearchHandler) {
            $this->nodeSourceSearchHandler->setHighlightingBsType($this->highlightingBsType);
            $this->nodeSourceSearchHandler->setHighlightingMethod($this->highlightingMethod);
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

    public function __invoke(Request $request): SearchEnginePaginator
    {
        try {
            $entityListManager = new SearchEngineListManager(
                $request,
                $this->getSearchHandler(),
                $this->getCriteria($request),
                true
            );

            return new SearchEnginePaginator($entityListManager);
        } catch (SolrServerNotAvailableException $e) {
            throw new ServiceUnavailableHttpException(previous: $e);
        } catch (SolrServerNotConfiguredException $e) {
            throw new NotFoundHttpException(previous: $e);
        }
    }
}
