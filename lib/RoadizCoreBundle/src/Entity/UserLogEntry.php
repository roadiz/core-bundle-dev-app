<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;

/**
 * Add User to Gedmo\Loggable\Entity\LogEntry
 */
#[
    ORM\Entity(repositoryClass: LogEntryRepository::class),
    ORM\Table(name: "user_log_entries", options: ["row_format" => "DYNAMIC"]),
    ORM\Index(columns: ["object_class"], name: "log_class_lookup_idx"),
    ORM\Index(columns: ["logged_at"], name: "log_date_lookup_idx"),
    ORM\Index(columns: ["username"], name: "log_user_lookup_idx"),
    ORM\Index(columns: ["object_id", "object_class", "version"], name: "log_version_lookup_idx")
]
class UserLogEntry extends AbstractLogEntry
{
    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: 'RZ\Roadiz\CoreBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', unique: false, onDelete: 'SET NULL')]
    protected ?User $user = null;

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     *
     * @return UserLogEntry
     */
    public function setUser(?User $user): UserLogEntry
    {
        $this->user = $user;

        return $this;
    }
}
