<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\CreateArticleType;
use App\GeneratedEntity\NSArticle;
use App\Model\CreateArticleInput;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\RozierBundle\Controller\AbstractSingleNodeTypeController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends AbstractSingleNodeTypeController<NSArticle, CreateArticleInput>
 */
final class ArticleController extends AbstractSingleNodeTypeController
{
    #[\Override]
    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_NODES';
    }

    #[\Override]
    protected function getDefaultRouteName(): string
    {
        return 'appArticlesListPage';
    }

    #[\Override]
    protected function getBulkPublishRouteName(): ?string
    {
        return 'appArticlesBulkPublishPage';
    }

    #[\Override]
    protected function getBulkUnpublishRouteName(): ?string
    {
        return 'appArticlesBulkUnpublishPage';
    }

    #[\Override]
    protected function getBulkDeleteRouteName(): ?string
    {
        return 'appArticlesBulkDeletePage';
    }

    #[\Override]
    protected function getShadowRootNodeName(): string
    {
        return 'articles';
    }

    #[\Override]
    protected function getNodeTypeName(): string
    {
        return 'Article';
    }

    #[\Override]
    protected function getEntityClass(): string
    {
        return NSArticle::class;
    }

    #[\Override]
    protected function createInputDto(): object
    {
        return new CreateArticleInput();
    }

    #[\Override]
    protected function getFormType(): string
    {
        return CreateArticleType::class;
    }

    #[\Override]
    protected function populateItem(object $input, Request $request): NodesSources
    {
        $item = parent::populateItem($input, $request);

        $item->setTitle($input->getTitle());
        $item->setPublishedAt(null);
        $item->getNode()->setTags($input->getTags() ?? []);

        return $item;
    }
}
