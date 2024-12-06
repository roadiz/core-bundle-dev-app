<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityHandler;

use RZ\Roadiz\Core\Handlers\AbstractHandler;
use RZ\Roadiz\CoreBundle\Entity\Group;
use RZ\Roadiz\CoreBundle\Entity\Role;

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

        foreach ($newGroup->getRolesEntities() as $newRole) {
            if (false === in_array($newRole->getName(), $existingRolesNames)) {
                /** @var Role|null $role */
                $role = $this->objectManager->getRepository(Role::class)
                                             ->findOneByName($newRole->getName());
                $this->getGroup()->addRoleEntity($role);
            }
        }
    }
}
