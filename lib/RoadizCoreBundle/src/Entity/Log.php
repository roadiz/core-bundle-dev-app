<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Monolog\Logger;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\CoreBundle\Repository\LogRepository;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\Entity(repositoryClass: LogRepository::class),
    ORM\Table(name: "log"),
    ORM\Index(columns: ["datetime"]),
    ORM\Index(columns: ["entity_class"]),
    ORM\Index(columns: ["entity_class", "entity_id"]),
    ORM\Index(columns: ["entity_class", "datetime"], name: "log_entity_class_datetime"),
    ORM\Index(columns: ["entity_class", "entity_id", "datetime"], name: "log_entity_class_id_datetime"),
    ORM\Index(columns: ["username", "datetime"], name: "log_username_datetime"),
    ORM\Index(columns: ["user_id", "datetime"], name: "log_user_datetime"),
    ORM\Index(columns: ["level", "datetime"], name: "log_level_datetime"),
    ORM\Index(columns: ["channel", "datetime"], name: "log_channel_datetime"),
    ORM\Index(columns: ["level"]),
    ORM\Index(columns: ["username"]),
    ORM\Index(columns: ["channel"]),
    ORM\HasLifecycleCallbacks
]
class Log extends AbstractEntity
{
    #[ORM\Column(name: 'user_id', type: 'string', length: 36, unique: false, nullable: true)]
    #[SymfonySerializer\Groups(['log_user'])]
    #[Serializer\Groups(['log_user'])]
    // @phpstan-ignore-next-line
    protected int|string|null $userId = null;

    #[ORM\Column(name: 'username', type: 'string', length: 255, nullable: true)]
    #[SymfonySerializer\Groups(['log_user'])]
    #[Serializer\Groups(['log_user'])]
    #[Assert\Length(max: 255)]
    protected ?string $username = null;

    #[ORM\Column(name: 'message', type: 'text')]
    #[SymfonySerializer\Groups(['log'])]
    #[Serializer\Groups(['log'])]
    protected string $message = '';

    #[ORM\Column(name: 'level', type: 'integer', nullable: false)]
    #[SymfonySerializer\Groups(['log'])]
    #[Serializer\Groups(['log'])]
    protected int $level = Logger::DEBUG;

    #[ORM\Column(name: 'datetime', type: 'datetime', nullable: false)]
    #[SymfonySerializer\Groups(['log'])]
    #[Serializer\Groups(['log'])]
    protected \DateTime $datetime;

    #[ORM\Column(name: 'client_ip', type: 'string', length: 46, unique: false, nullable: true)]
    #[SymfonySerializer\Groups(['log'])]
    #[Serializer\Groups(['log'])]
    #[Assert\Length(max: 46)]
    protected ?string $clientIp = null;

    #[ORM\Column(name: 'channel', type: 'string', length: 64, unique: false, nullable: true)]
    #[SymfonySerializer\Groups(['log'])]
    #[Serializer\Groups(['log'])]
    #[Assert\Length(max: 64)]
    protected ?string $channel = null;

    /**
     * @var class-string|null
     */
    #[ORM\Column(name: 'entity_class', type: 'string', length: 255, unique: false, nullable: true)]
    #[SymfonySerializer\Groups(['log'])]
    #[Serializer\Groups(['log'])]
    #[Assert\Length(max: 255)]
    // @phpstan-ignore-next-line
    protected ?string $entityClass = null;

    /**
     * @var string|int|null
     */
    #[ORM\Column(name: 'entity_id', type: 'string', length: 36, unique: false, nullable: true)]
    #[SymfonySerializer\Groups(['log'])]
    #[Serializer\Groups(['log'])]
    #[Assert\Length(max: 36)]
    // @phpstan-ignore-next-line
    protected string|int|null $entityId = null;

    #[ORM\Column(name: 'additional_data', type: 'json', unique: false, nullable: true)]
    #[SymfonySerializer\Groups(['log'])]
    #[Serializer\Groups(['log'])]
    protected ?array $additionalData = null;

    /**
     * @param int    $level
     * @param string $message
     *
     * @throws \Exception
     */
    public function __construct(int $level, string $message)
    {
        $this->level = $level;
        $this->message = $message;
        $this->datetime = new \DateTime("now");
    }

    /**
     * @return int|string|null
     */
    public function getUserId(): int|string|null
    {
        return $this->userId;
    }

    /**
     * @param int|string|null $userId
     * @return Log
     */
    public function setUserId(int|string|null $userId): Log
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @param User $user
     *
     * @return Log
     */
    public function setUser(User $user): Log
    {
        $this->userId = $user->getId();
        $this->username = $user->getUsername();
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     *
     * @return Log
     */
    public function setUsername(?string $username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return \DateTime
     */
    public function getDatetime(): \DateTime
    {
        return $this->datetime;
    }

    /**
     * BC setter.
     *
     * @param NodesSources|null $nodeSource
     * @return $this
     */
    public function setNodeSource(?NodesSources $nodeSource): Log
    {
        if (null !== $nodeSource) {
            $this->entityClass = NodesSources::class;
            $this->entityId = $nodeSource->getId();
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    /**
     * @param string|null $clientIp
     * @return Log
     */
    public function setClientIp(?string $clientIp): Log
    {
        $this->clientIp = $clientIp;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getAdditionalData(): ?array
    {
        return $this->additionalData;
    }

    /**
     * @param array|null $additionalData
     *
     * @return Log
     */
    public function setAdditionalData(?array $additionalData): Log
    {
        $this->additionalData = $additionalData;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getChannel(): ?string
    {
        return $this->channel;
    }

    /**
     * @param string|null $channel
     *
     * @return Log
     */
    public function setChannel(?string $channel): Log
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return class-string|null
     */
    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }

    /**
     * @param class-string|null $entityClass
     * @return Log
     */
    public function setEntityClass(?string $entityClass): Log
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    /**
     * @return int|string|null
     */
    public function getEntityId(): int|string|null
    {
        return $this->entityId;
    }

    /**
     * @param int|string|null $entityId
     * @return Log
     */
    public function setEntityId(int|string|null $entityId): Log
    {
        $this->entityId = $entityId;
        return $this;
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->datetime = new \DateTime("now");
    }
}
