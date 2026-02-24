<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use RZ\Roadiz\CoreBundle\Repository\UserLogEntryRepository;

/**
 * Add User to Gedmo\Loggable\Entity\LogEntry.
 */
#[ORM\Entity(repositoryClass: UserLogEntryRepository::class),
    ORM\Table(name: 'user_log_entries', options: ['row_format' => 'DYNAMIC']),
    ORM\Index(columns: ['object_class'], name: 'log_class_lookup_idx'),
    ORM\Index(columns: ['logged_at'], name: 'log_date_lookup_idx'),
    ORM\Index(columns: ['username'], name: 'log_user_lookup_idx'),
    ORM\Index(columns: ['object_id', 'object_class', 'version'], name: 'log_version_lookup_idx')]
class UserLogEntry extends AbstractLogEntry
{
}
