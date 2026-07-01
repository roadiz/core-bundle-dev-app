<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Controller\TranslationAwareControllerTrait;
use RZ\Roadiz\CoreBundle\Api\ListManager\SearchEngineListManager;
use RZ\Roadiz\CoreBundle\Api\ListManager\SearchEnginePaginator;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

#[AsController]
class NodesSourcesSearchController extends AbstractController
{
    use TranslationAwareControllerTrait;

    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly ManagerRegistry $managerRegistry,
        protected readonly PreviewResolverInterface $previewResolver,
        protected readonly ?NodeSourceSearchHandlerInterface $nodeSourceSearchHandler,
        protected readonly int $highlightingFragmentSize = 200,
        protected readonly SolrHighlightingBsTypeEnum $highlightingBsType = SolrHighlightingBsTypeEnum::WORD,
        protected readonly SolrHighlightingMethodEnum $highlightingMethod = SolrHighlightingMethodEnum::UNIFIED,
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

    protected function getTranslationFromRequest(): ?TranslationInterface
    {
        $request = $this->requestStack->getMainRequest();
        if (null !== $request) {
            return $this->getTranslation($request);
        }

        return null;
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

        if ($this->nodeSourceSearchHandler instanceof AbstractSearchHandler) {
            $this->nodeSourceSearchHandler->setHighlightingBsType($this->highlightingBsType);
            $this->nodeSourceSearchHandler->setHighlightingMethod($this->highlightingMethod);
        }

        return $this->nodeSourceSearchHandler;
    }

    /**
     * @return string[]
     */
    protected function getAllowedNodeTypes(): array
    {
        return [
            'Page',
            'Article',
            'BlogPost',
            'ArticleContainer',
            'Offer',
        ];
    }

    protected function getCriteria(): array
    {
        $criteria = [
            'nodeType' => $this->getAllowedNodeTypes(),
            'translation' => $this->getTranslationFromRequest(),
        ];

        if ($this->getPreviewResolver()->isPreview()) {
            /*
             * Previewers are allowed to see draft, pending and published content,
             * including not-yet-published (embargoed) items. Setting an explicit
             * status override also disables NodeSourceSearchHandler's default
             * `published_at_dt:[* TO NOW/MINUTE]` temporal filter.
             */
            $criteria['status'] = ['<=', NodeStatus::PUBLISHED];
        } else {
            /*
             * Default visitors only get currently published and visible content.
             * NodeSourceSearchHandler already defaults to `node_status_i:PUBLISHED`
             * and `published_at_dt:[* TO NOW/MINUTE]` when no status override is
             * requested, so we only add the visibility constraint here. Passing an
             * exact PHP-computed `publishedAt` timestamp would produce a unique fq
             * string on every request, defeating Solr's filter cache reuse.
             */
            $criteria['visible'] = true;
        }

        return $criteria;
    }

    public function __invoke(Request $request): SearchEnginePaginator
    {
        try {
            $entityListManager = new SearchEngineListManager(
                $request,
                $this->getSearchHandler(),
                $this->getCriteria(),
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
