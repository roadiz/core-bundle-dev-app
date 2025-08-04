<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Explorer;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UserExplorerItem extends AbstractExplorerItem
{
    public function __construct(
        private readonly User $user,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[\Override]
    public function getId(): int|string
    {
        return $this->user->getId() ?? throw new \RuntimeException('Entity must have an ID');
    }

    #[\Override]
    public function getAlternativeDisplayable(): ?string
    {
        return $this->user->getEmail();
    }

    #[\Override]
    public function getDisplayable(): string
    {
        $fullName = trim(
            ($this->user->getFirstName() ?? '').
            ' '.
            ($this->user->getLastName() ?? '')
        );
        if ('' !== $fullName) {
            return $fullName;
        }

        return $this->user->getUsername();
    }

    #[\Override]
    public function getOriginal(): User
    {
        return $this->user;
    }

    #[\Override]
    protected function getEditItemPath(): ?string
    {
        return $this->urlGenerator->generate('usersEditPage', [
            'id' => $this->user->getId(),
        ]);
    }
}
