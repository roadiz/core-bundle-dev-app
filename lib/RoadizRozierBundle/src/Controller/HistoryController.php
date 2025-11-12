<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use Monolog\Logger;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\Logger\Entity\Log;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\UserVoter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class HistoryController extends AbstractController
{
    public static array $levelToHuman = [
        Logger::EMERGENCY => 'emergency',
        Logger::CRITICAL => 'critical',
        Logger::ALERT => 'alert',
        Logger::ERROR => 'error',
        Logger::WARNING => 'warning',
        Logger::NOTICE => 'notice',
        Logger::INFO => 'info',
        Logger::DEBUG => 'debug',
    ];

    public function __construct(
        private readonly EntityListManagerFactoryInterface $entityListManagerFactory,
    ) {
    }

    /*
     * List all logs action.
     */
    #[Route(
        path: '/rz-admin/history',
        name: 'historyHomePage',
        methods: ['GET'],
    )]
    public function indexAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_LOGS');

        /*
         * Manage get request to filter list
         */
        $listManager = $this->entityListManagerFactory->createAdminEntityListManager(
            Log::class,
            [],
            ['datetime' => 'DESC']
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->setDisplayingAllNodesStatuses(true);
        $listManager->handle();

        return $this->render('@RoadizRozier/history/list.html.twig', [
            'logs' => $listManager->getEntities(),
            'levels' => self::$levelToHuman,
            'filters' => $listManager->getAssignation(),
        ]);
    }

    /**
     * List user logs action.
     */
    #[Route(
        path: '/rz-admin/history/user/{userId}',
        name: 'historyUserPage',
        requirements: ['userId' => '[0-9]+'],
        methods: ['GET'],
    )]
    public function userAction(
        #[MapEntity(
            expr: 'repository.find(userId)',
            message: 'User does not exist'
        )]
        User $user,
    ): Response {
        $this->denyAccessUnlessGranted(UserVoter::VIEW_HISTORY, $user);

        /*
         * Manage get request to filter list
         */
        $listManager = $this->entityListManagerFactory->createAdminEntityListManager(
            Log::class,
            ['userId' => $user->getId()],
            ['datetime' => 'DESC']
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->setDisplayingAllNodesStatuses(true);
        $listManager->setItemPerPage(30);
        $listManager->handle();

        return $this->render('@RoadizRozier/history/list.html.twig', [
            'user' => $user,
            'logs' => $listManager->getEntities(),
            'levels' => self::$levelToHuman,
            'filters' => $listManager->getAssignation(),
        ]);
    }
}
