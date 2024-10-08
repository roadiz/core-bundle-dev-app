<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization\Chroot;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Loops over NodeChrootResolver implementations to find the right one supporting
 * a given UserInterface or string User representation (from a Token for example).
 *
 * @package RZ\Roadiz\CoreBundle\Security\Authorization\Chroot
 */
final class NodeChrootChainResolver implements NodeChrootResolver
{
    public function __construct(private readonly array $resolvers)
    {
        foreach ($this->resolvers as $resolver) {
            if (!($resolver instanceof NodeChrootResolver)) {
                throw new \InvalidArgumentException('Resolver must implements ' . NodeChrootResolver::class);
            }
        }
    }

    /**
     * @param User|UserInterface|string|null $user
     *
     * @return Node|null
     */
    public function getChroot(mixed $user): ?Node
    {
        /** @var NodeChrootResolver $resolver */
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($user)) {
                return $resolver->getChroot($user);
            }
        }
        return null;
    }

    /**
     * @param User|UserInterface|string|null $user
     *
     * @return bool
     */
    public function supports(mixed $user): bool
    {
        /** @var NodeChrootResolver $resolver */
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($user)) {
                return true;
            }
        }
        return false;
    }
}
