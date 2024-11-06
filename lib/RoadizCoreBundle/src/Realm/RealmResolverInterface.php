<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Realm;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

interface RealmResolverInterface
{
    /**
     * @return bool Does current application has realms?
     */
    public function hasRealms(): bool;

    /**
     * @return bool Does current application has realms with serialization groups?
     */
    public function hasRealmsWithSerializationGroup(): bool;

    /**
     * @return RealmInterface[]
     */
    public function getRealms(?Node $node): array;

    /**
     * @return RealmInterface[]
     */
    public function getRealmsWithSerializationGroup(?Node $node): array;

    public function isGranted(RealmInterface $realm): bool;

    /**
     * @throws UnauthorizedHttpException
     */
    public function denyUnlessGranted(RealmInterface $realm): void;

    /**
     * @return RealmInterface[] return all realms granted to current user
     */
    public function getGrantedRealms(): array;

    /**
     * @return RealmInterface[] return all realms denied from current user
     */
    public function getDeniedRealms(): array;
}
