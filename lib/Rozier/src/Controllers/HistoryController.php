<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use Monolog\Logger;
use RZ\Roadiz\CoreBundle\Entity\Log;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

/**
 * Display CMS logs.
 */
class HistoryController extends RozierApp
{
    public static array $levelToHuman = [
        Logger::EMERGENCY => "emergency",
        Logger::CRITICAL => "critical",
        Logger::ALERT => "alert",
        Logger::ERROR => "error",
        Logger::WARNING => "warning",
        Logger::NOTICE => "notice",
        Logger::INFO => "info",
        Logger::DEBUG => "debug",
    ];

    /**
     * List all logs action.
     *
     * @param Request $request
     *
     * @return Response
     * @throws RuntimeError
     */
    public function indexAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_LOGS');

        /*
         * Manage get request to filter list
         */
        $listManager = $this->createEntityListManager(
            Log::class,
            [],
            ['datetime' => 'DESC']
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->setDisplayingAllNodesStatuses(true);
        $listManager->handle();

        $this->assignation['filters'] = $listManager->getAssignation();
        $this->assignation['logs'] = $listManager->getEntities();
        $this->assignation['levels'] = static::$levelToHuman;

        return $this->render('@RoadizRozier/history/list.html.twig', $this->assignation);
    }

    /**
     * List user logs action.
     *
     * @param Request $request
     * @param int|string $userId
     *
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function userAction(Request $request, int|string $userId): Response
    {
        $this->denyAccessUnlessGranted(['ROLE_BACKEND_USER', 'ROLE_ACCESS_LOGS']);

        if (
            !($this->isGranted(['ROLE_ACCESS_USERS', 'ROLE_ACCESS_LOGS'])
            || ($this->getUser() instanceof User && $this->getUser()->getId() === $userId))
        ) {
            throw $this->createAccessDeniedException("You don't have access to this page: ROLE_ACCESS_USERS");
        }

        /** @var User|null $user */
        $user = $this->em()->find(User::class, $userId);

        if (null === $user) {
            throw new ResourceNotFoundException();
        }

        /*
         * Manage get request to filter list
         */
        $listManager = $this->createEntityListManager(
            Log::class,
            ['userId' => $user->getId()],
            ['datetime' => 'DESC']
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->setDisplayingAllNodesStatuses(true);
        $listManager->setItemPerPage(30);
        $listManager->handle();

        $this->assignation['filters'] = $listManager->getAssignation();
        $this->assignation['logs'] = $listManager->getEntities();
        $this->assignation['levels'] = static::$levelToHuman;
        $this->assignation['user'] = $user;

        return $this->render('@RoadizRozier/history/list.html.twig', $this->assignation);
    }
}
