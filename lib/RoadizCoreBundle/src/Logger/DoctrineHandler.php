<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Logger;

use Doctrine\Persistence\ManagerRegistry;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use RZ\Roadiz\CoreBundle\Entity\Log;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * A log system which store message in database.
 */
final class DoctrineHandler extends AbstractProcessingHandler
{
    protected ManagerRegistry $managerRegistry;
    protected TokenStorageInterface $tokenStorage;
    protected RequestStack $requestStack;

    public function __construct(
        ManagerRegistry $managerRegistry,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        $level = Logger::INFO,
        $bubble = true
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->managerRegistry = $managerRegistry;

        parent::__construct($level, $bubble);
    }

    /**
     * @return TokenStorageInterface
     */
    public function getTokenStorage(): TokenStorageInterface
    {
        return $this->tokenStorage;
    }
    /**
     * @param TokenStorageInterface $tokenStorage
     *
     * @return $this
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage): DoctrineHandler
    {
        $this->tokenStorage = $tokenStorage;
        return $this;
    }

    /**
     * @param array  $record
     */
    public function write(array $record): void
    {
        try {
            $manager = $this->managerRegistry->getManagerForClass(Log::class);
            if (null === $manager || !$manager->isOpen()) {
                return;
            }

            $log = new Log(
                $record['level'],
                $record['message']
            );

            $log->setChannel((string) $record['channel']);
            $data = $record['extra'];
            if (isset($record['context']['exception']) && $record['context']['exception'] instanceof \Exception) {
                $data = array_merge(
                    $data,
                    [
                        get_class($record['context']['exception']) => $record['context']['exception']->getMessage()
                    ]
                );
            }
            if (isset($record['context']['request'])) {
                $data = array_merge(
                    $data,
                    $record['context']['request']
                );
            }
            if (isset($record['context']['username'])) {
                $data = array_merge(
                    $data,
                    ['username' => $record['context']['username']]
                );
            }
            $log->setAdditionalData($data);

            /*
             * Use available securityAuthorizationChecker to provide a valid user
             */
            if (
                null !== $this->getTokenStorage() &&
                null !== $token = $this->getTokenStorage()->getToken()
            ) {
                $user = $token->getUser();
                if ($user instanceof UserInterface) {
                    if ($user instanceof User) {
                        $log->setUser($user);
                    } else {
                        $log->setUsername($user->getUsername());
                    }
                } else {
                    $log->setUsername($token->getUsername());
                }
            }

            /*
             * Add client IP to log if it’s an HTTP request
             */
            if (null !== $this->requestStack->getMainRequest()) {
                $log->setClientIp($this->requestStack->getMainRequest()->getClientIp());
            }

            /*
             * Add a related node-source entity
             */
            if (
                isset($record['context']['source']) &&
                $record['context']['source'] instanceof NodesSources
            ) {
                $log->setNodeSource($record['context']['source']);
            }

            $manager->persist($log);
            $manager->flush();
        } catch (\Exception $e) {
            /*
             * Need to prevent SQL errors over throwing
             * if PDO has faulted
             */
        }
    }
}
