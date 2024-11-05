<?php

declare(strict_types=1);

namespace Themes\Rozier\Explorer;

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

    public function getId(): int|string
    {
        return $this->user->getId() ?? throw new \RuntimeException('Entity must have an ID');
    }

    public function getAlternativeDisplayable(): ?string
    {
        return $this->user->getEmail();
    }

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

    public function getOriginal(): User
    {
        return $this->user;
    }

    protected function getEditItemPath(): ?string
    {
        return $this->urlGenerator->generate('usersEditPage', [
            'id' => $this->user->getId(),
        ]);
    }
}
