<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Realm;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

interface RealmResolverInterface
{
    /**
     * @param Node|null $node
     * @return RealmInterface[]
     */
    public function getRealms(?Node $node): array;
    public function isGranted(RealmInterface $realm): bool;

    /**
     * @param RealmInterface $realm
     * @return void
     * @throws UnauthorizedHttpException
     */
    public function denyUnlessGranted(RealmInterface $realm): void;
}
