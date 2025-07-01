<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Group;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final readonly class GroupNormalizer implements DenormalizerInterface
{
    public const string PERSIST_NEW_ENTITIES = 'persist_new_entities';

    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Group::class => true,
        ];
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Group
    {
        $name = $data['name'];

        if (!is_string($name)) {
            throw new \InvalidArgumentException('Group name must be a string.');
        }

        $group = $this->managerRegistry->getRepository(Group::class)->findOneByName($name);

        if (null === $group) {
            $group = new Group();
            $group->setName($name);
            if ($context[self::PERSIST_NEW_ENTITIES] ?? false) {
                $this->managerRegistry->getManagerForClass(Group::class)->persist($group);
            }
        }

        if (\is_array($data['roles'])) {
            foreach ($data['roles'] as $roleName) {
                if (!is_string($roleName)) {
                    continue;
                }
                $group->setRoles([
                    ...$group->getRoles(),
                    $roleName,
                ]);
            }
        }

        return $group;
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return is_array($data) && array_key_exists('roles', $data) && array_key_exists('name', $data);
    }
}
