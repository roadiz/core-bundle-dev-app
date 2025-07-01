<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityHandler;

use RZ\Roadiz\Core\Handlers\AbstractHandler;
use RZ\Roadiz\CoreBundle\Entity\Group;

/**
 * Handle operations with Group entities.
 */
final class GroupHandler extends AbstractHandler
{
    private ?Group $group = null;

    public function getGroup(): Group
    {
        if (null === $this->group) {
            throw new \BadMethodCallException('Group is null');
        }

        return $this->group;
    }

    /**
     * @return $this
     */
    public function setGroup(Group $group): self
    {
        $this->group = $group;

        return $this;
    }

    /**
     * This method does not flush ORM. You'll need to manually call it.
     */
    public function diff(Group $newGroup): void
    {
        if ('' != $newGroup->getName()) {
            $this->getGroup()->setName($newGroup->getName());
        }

        $existingRolesNames = $this->getGroup()->getRoles();

        foreach ($newGroup->getRoles() as $newRole) {
            if (false === in_array($newRole, $existingRolesNames)) {
                $this->getGroup()->setRoles([
                    ...$existingRolesNames,
                    $newRole,
                ]);
            }
        }
    }
}
