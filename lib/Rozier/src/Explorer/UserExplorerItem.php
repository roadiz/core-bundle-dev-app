<?php

declare(strict_types=1);

namespace Themes\Rozier\Explorer;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UserExplorerItem extends AbstractExplorerItem
{
    private User $user;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(User $user, UrlGeneratorInterface $urlGenerator)
    {
        $this->user = $user;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritDoc
     */
    public function getId(): int|string
    {
        return $this->user->getId() ?? throw new \RuntimeException('Entity must have an ID');
    }

    /**
     * @inheritDoc
     */
    public function getAlternativeDisplayable(): ?string
    {
        return $this->user->getEmail();
    }

    /**
     * @inheritDoc
     */
    public function getDisplayable(): string
    {
        $fullName = trim(
            ($this->user->getFirstName() ?? '') .
            ' ' .
            ($this->user->getLastName() ?? '')
        );
        if ($fullName !== '') {
            return $fullName;
        }
        return $this->user->getUsername();
    }

    /**
     * @inheritDoc
     */
    public function getOriginal(): User
    {
        return $this->user;
    }

    protected function getEditItemPath(): ?string
    {
        return $this->urlGenerator->generate('usersEditPage', [
            'userId' => $this->user->getId()
        ]);
    }
}
