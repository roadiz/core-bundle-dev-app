<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\TwoFactorBundle\Security\Provider\TwoFactorUserProviderInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'users:list',
    description: 'List all users or just one'
)]
final class UsersCommand extends \RZ\Roadiz\CoreBundle\Console\UsersCommand
{
    private TwoFactorUserProviderInterface $twoFactorUserProvider;

    public function __construct(
        TwoFactorUserProviderInterface $twoFactorUserProvider,
        ManagerRegistry $managerRegistry,
        string $name = null
    ) {
        parent::__construct($managerRegistry, $name);
        $this->twoFactorUserProvider = $twoFactorUserProvider;
    }

    protected function getUserTableRow(User $user): array
    {
        $twoFactorUser = $this->twoFactorUserProvider->getFromUser($user);
        return [
            'Id' => $user->getId(),
            'Username' => $user->getUsername(),
            'Email' => $user->getEmail(),
            'Disabled' => (!$user->isEnabled() ? 'X' : ''),
            'Expired' => (!$user->isAccountNonExpired() ? 'X' : ''),
            'Locked' => (!$user->isAccountNonLocked() ? 'X' : ''),
            'Groups' => implode(' ', $user->getGroupNames()),
            '2FA enabled' => null !== $twoFactorUser && $twoFactorUser->isTotpAuthenticationEnabled() ? 'X' : '',
        ];
    }
}
