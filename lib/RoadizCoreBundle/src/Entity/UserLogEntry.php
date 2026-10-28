<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\LogEntryInterface;
use RZ\Roadiz\CoreBundle\Repository\UserLogEntryRepository;

/**
 * Add User to Gedmo\Loggable\Entity\LogEntry.
 */
#[ORM\Entity(repositoryClass: UserLogEntryRepository::class)]
#[ORM\Table(name: 'user_log_entries', options: ['row_format' => 'DYNAMIC'])]
#[ORM\Index(name: 'log_class_lookup_idx', columns: ['object_class'])]
#[ORM\Index(name: 'log_date_lookup_idx', columns: ['logged_at'])]
#[ORM\Index(name: 'log_user_lookup_idx', columns: ['username'])]
#[ORM\Index(name: 'log_version_lookup_idx', columns: ['object_id', 'object_class', 'version'])]
class UserLogEntry implements LogEntryInterface
{
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    /**
     * @var self::ACTION_CREATE|self::ACTION_UPDATE|self::ACTION_REMOVE
     */
    #[ORM\Column(type: Types::STRING, length: 8)]
    protected string $action;

    #[ORM\Column(name: 'logged_at', type: Types::DATETIME_MUTABLE)]
    protected \DateTime $loggedAt;

    #[ORM\Column(name: 'object_id', length: 64, nullable: true)]
    protected ?string $objectId = null;

    /**
     * @var class-string
     */
    #[ORM\Column(name: 'object_class', type: Types::STRING, length: 191)]
    protected string $objectClass;

    #[ORM\Column(type: Types::INTEGER)]
    protected int $version;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    protected ?array $data = null;

    #[ORM\Column(length: 191, nullable: true)]
    protected ?string $username = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): UserLogEntry
    {
        $this->id = $id;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): UserLogEntry
    {
        $this->action = $action;

        return $this;
    }

    public function getLoggedAt(): ?\DateTime
    {
        return $this->loggedAt;
    }

    public function setLoggedAt(): UserLogEntry
    {
        $this->loggedAt = new \DateTime();

        return $this;
    }

    public function getObjectId(): ?string
    {
        return $this->objectId;
    }

    public function setObjectId(?string $objectId): UserLogEntry
    {
        $this->objectId = $objectId;

        return $this;
    }

    public function getObjectClass(): ?string
    {
        return $this->objectClass;
    }

    public function setObjectClass(string $objectClass): UserLogEntry
    {
        $this->objectClass = $objectClass;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(int $version): UserLogEntry
    {
        $this->version = $version;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): UserLogEntry
    {
        $this->data = $data;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): UserLogEntry
    {
        $this->username = $username;

        return $this;
    }
}
