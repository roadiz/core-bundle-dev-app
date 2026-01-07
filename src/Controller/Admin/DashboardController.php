<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\GeneratedEntity\NSArticle;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\Repository\LogRepository;
use RZ\Roadiz\CoreBundle\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class DashboardController extends AbstractController
{
    private const int ITEM_COUNT = 5;

    public function __construct(
        private readonly LogRepository $logRepository,
        private readonly TagRepository $tagRepository,
        private readonly EntityListManagerFactoryInterface $entityListManagerFactory,
    ) {
    }

    public function indexAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        $assignation = [];
        $assignation['latestLogs'] = $this->logRepository->findLatestByNodesSources(8);

        // @phpstan-ignore-next-line
        $clientsTag = $this->tagRepository->findOneByTagName('clients');
        $assignation['clientsTag'] = $clientsTag;
        $assignation['clients'] = $this->tagRepository->findByParentWithDefaultTranslation($clientsTag);

        $assignation['articles'] = $this->entityListManagerFactory
            ->createEntityListManager(
                NSArticle::class,
                ordering: ['node.createdAt' => 'DESC']
            )
            ->setPage(1)
            ->setItemPerPage(self::ITEM_COUNT)
            ->getEntities();

        return $this->render('@RoadizRozier/dashboard/index.html.twig', $assignation);
    }
}
