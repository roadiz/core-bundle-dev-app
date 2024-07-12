<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Logger;

use Doctrine\Persistence\ManagerRegistry;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Logger\Entity\Log;
use RZ\Roadiz\Documents\Models\DocumentInterface;
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
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly RequestStack $requestStack,
        private readonly DocumentUrlGeneratorInterface $documentUrlGenerator,
        $level = Logger::INFO,
        $bubble = true
    ) {
        parent::__construct($level, $bubble);
    }

    protected function getThumbnailSourcePath(?DocumentInterface $thumbnail): ?string
    {
        if (null === $thumbnail || $thumbnail->isPrivate()) {
            return null;
        }
        return $this->documentUrlGenerator
            ->setDocument($thumbnail)
            ->setOptions([
                "fit" => "150x150",
                "quality" => 70,
            ])
            ->getUrl();
    }

    protected function populateForNode(Node $value, Log $log, array &$data): void
    {
        $log->setEntityClass(Node::class);
        $log->setEntityId($value->getId());
        $data = array_merge(
            $data,
            [
                'node_id' => $value->getId(),
                'entity_title' => $value->getNodeName(),
            ]
        );
        $nodeSource = $value->getNodeSources()->first() ?: null;
        if (null !== $nodeSource) {
            $data = array_merge(
                $data,
                [
                    'node_source_id' => $nodeSource->getId(),
                    'translation_id' => $nodeSource->getTranslation()->getId(),
                    'entity_title' => $nodeSource->getTitle() ?? $value->getNodeName(),
                ]
            );
        }

        $thumbnailSrc = $this->getThumbnailSourcePath($nodeSource?->getOneDisplayableDocument());
        if (null !== $thumbnailSrc) {
            $data = array_merge(
                $data,
                [
                    'entity_thumbnail_src' => $thumbnailSrc,
                ]
            );
        }
    }

    protected function populateForNodesSources(NodesSources $value, Log $log, array &$data): void
    {
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
        $thumbnailSrc = $this->getThumbnailSourcePath($thumbnail);
        if (null !== $thumbnailSrc) {
            $data = array_merge(
                $data,
                [
                    'entity_thumbnail_src' => $thumbnailSrc,
                ]
            );
        }
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
                    if ($value instanceof Node) {
                        $this->populateForNode($value, $log, $data);
                    } elseif ($value instanceof NodesSources) {
                        $this->populateForNodesSources($value, $log, $data);
                    } elseif ($key === 'entity' && $value instanceof PersistableInterface) {
                        $log->setEntityClass(get_class($value));
                        $log->setEntityId($value->getId());

                        $texteable = ['getTitle', 'getName', '__toString'];
                        foreach ($texteable as $method) {
                            if (method_exists($value, $method)) {
                                $data = array_merge(
                                    $data,
                                    [
                                        'entity_title' => $value->{$method}()
                                    ]
                                );
                                break;
                            }
                        }
                    }
                    if ($value instanceof \Exception) {
                        $data = array_merge(
                            $data,
                            [
                                'exception_class' => get_class($value),
                                'message' => $value->getMessage()
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
            if (null !== $token = $this->tokenStorage->getToken()) {
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
                        $log->setUsername($user->getUserIdentifier());
                    }
                } else {
                    $log->setUsername($token->getUserIdentifier());
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
