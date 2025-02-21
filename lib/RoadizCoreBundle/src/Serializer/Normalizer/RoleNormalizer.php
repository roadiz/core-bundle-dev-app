<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Group;
use RZ\Roadiz\CoreBundle\Entity\Role;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final readonly class RoleNormalizer implements DenormalizerInterface
{
    public const PERSIST_NEW_ENTITIES = 'persist_new_entities';

    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Role::class => true,
        ];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Role
    {
        $name = $data['name'];

        if (!is_string($name)) {
            throw new \InvalidArgumentException('Role name must be a string.');
        }

        /**
         * Role findOneByName method already persist and flush new entities.
         */
        $role = $this->managerRegistry->getRepository(Role::class)->findOneByName($name);

        if (array_key_exists('groups', $data) && \is_array($data['groups'])) {
            foreach ($data['groups'] as $group) {
                if (\is_string($group)) {
                    $groupName = $group;
                }
                if (\is_array($group) && array_key_exists('name', $group)) {
                    $groupName = $group['name'];
                }

                if (!isset($groupName)) {
                    continue;
                }

                /** @var Group|null $group */
                $group = $this->managerRegistry->getRepository(Group::class)->findOneByName($groupName);

                if (null === $group) {
                    $group = new Group();
                    $group->setName($groupName);
                    if ($context[self::PERSIST_NEW_ENTITIES] ?? false) {
                        $this->managerRegistry->getManagerForClass(Group::class)->persist($group);
                        $this->managerRegistry->getManagerForClass(Group::class)->flush();
                    }
                }
                $group->addRoleEntity($role);
                $role->addGroup($group);
            }
        }

        return $role;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return is_array($data) && array_key_exists('name', $data);
    }
}
