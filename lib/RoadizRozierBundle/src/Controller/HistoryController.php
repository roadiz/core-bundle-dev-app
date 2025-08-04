<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Monolog\Logger;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\Logger\Entity\Log;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

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
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    /**
     * List all logs action.
     */
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
    public function userAction(Request $request, int|string $userId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        if (
            !($this->isGranted('ROLE_ACCESS_USERS') || $this->isGranted('ROLE_ACCESS_LOGS'))
            || ($this->getUser() instanceof User && $this->getUser()->getId() === $userId)
        ) {
            throw $this->createAccessDeniedException("You don't have access to this page: ROLE_ACCESS_USERS");
        }

        /** @var User|null $user */
        $user = $this->managerRegistry->getRepository(User::class)->find($userId);

        if (null === $user) {
            throw new ResourceNotFoundException();
        }

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
