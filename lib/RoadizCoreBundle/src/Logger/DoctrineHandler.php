<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Logger;

use Doctrine\Persistence\ManagerRegistry;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\Log;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
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
    private DocumentUrlGeneratorInterface $documentUrlGenerator;

    public function __construct(
        ManagerRegistry $managerRegistry,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        DocumentUrlGeneratorInterface $documentUrlGenerator,
        $level = Logger::INFO,
        $bubble = true
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->managerRegistry = $managerRegistry;

        parent::__construct($level, $bubble);
        $this->documentUrlGenerator = $documentUrlGenerator;
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
            $context = $record['context'];

            if (\is_array($context)) {
                foreach ($context as $key => $value) {
                    if ($value instanceof NodesSources) {
                        $log->setEntityClass(NodesSources::class);
                        $log->setEntityId($value->getId());
                        $data = array_merge(
                            $data,
                            [
                                'node_source_id' => $value->getId(),
                                'node_id' => $value->getNode()->getId(),
                                'translation_id' => $value->getTranslation()->getId(),
                                'entity_title' => $value->getTitle(),
                            ]
                        );

                        $thumbnail = $value->getOneDisplayableDocument();
                        if (null !== $thumbnail) {
                            $thumbnailSrc = $this->documentUrlGenerator
                                ->setDocument($thumbnail)
                                ->setOptions([
                                    "fit" => "150x150",
                                    "quality" => 70,
                                ])
                                ->getUrl();

                            $data = array_merge(
                                $data,
                                [
                                    'entity_thumbnail_src' => $thumbnailSrc,
                                ]
                            );
                        }
                    } elseif ($key === 'entity' && $value instanceof PersistableInterface) {
                        $log->setEntityClass(get_class($value));
                        $log->setEntityId($value->getId());
                    }
                    if ($value instanceof \Exception) {
                        $data = array_merge(
                            $data,
                            [
                                get_class($value) => $value->getMessage()
                            ]
                        );
                    }
                    if ($value instanceof Request) {
                        $data = array_merge(
                            $data,
                            [
                                'uri' => $value->getUri(),
                                'schemeHost' => $value->getSchemeAndHttpHost(),
                            ]
                        );
                    }
                    if ($key === 'request' && \is_array($value)) {
                        $data = array_merge(
                            $data,
                            $value
                        );
                    }
                    if (\is_string($value) && !empty($value) && !\is_numeric($key)) {
                        $data = array_merge(
                            $data,
                            [$key => $value]
                        );
                    }
                    if (\is_string($value) && !empty($value) && \in_array($key, ['user', 'username'])) {
                        $log->setUsername($value);
                    }
                }
            }

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
                        $data = array_merge(
                            $data,
                            [
                                'user_email' => $user->getEmail(),
                                'user_public_name' => $user->getPublicName(),
                                'user_picture_url' => $user->getPictureUrl(),
                                'user_id' => $user->getId()
                            ]
                        );
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

            $log->setAdditionalData($data);

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
