<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Bag;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Bag\LazyParameterBag;
use RZ\Roadiz\CoreBundle\Entity\Role;
use RZ\Roadiz\CoreBundle\Repository\RoleRepository;

final class Roles extends LazyParameterBag
{
    private ?RoleRepository $repository = null;

    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
        parent::__construct();
    }

    public function getRepository(): RoleRepository
    {
        if (null === $this->repository) {
            $this->repository = $this->managerRegistry->getRepository(Role::class);
        }
        return $this->repository;
    }

    protected function populateParameters(): void
    {
        try {
            $roles = $this->getRepository()->findAll();
            $this->parameters = [];
            /** @var Role $role */
            foreach ($roles as $role) {
                $this->parameters[$role->getRole()] = $role;
            }
        } catch (\Exception $e) {
            $this->parameters = [];
        }
        $this->ready = true;
    }

    /**
     * Get role by name or create it if non-existent.
     *
     * @param string $key
     * @param null   $default
     *
     * @return Role
     */
    public function get(string $key, $default = null): Role
    {
        $role = parent::get($key, $default);

        if (null === $role) {
            $role = new Role($key);
            $roleManager = $this->managerRegistry->getManagerForClass(Role::class);
            $roleManager->persist($role);
            $roleManager->flush();
        }

        return $role;
    }
}
