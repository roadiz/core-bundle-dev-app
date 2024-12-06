<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Preview\User;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

interface PreviewUserProviderInterface
{
    /**
     * @throws AccessDeniedException If original user is not allowed to preview
     */
    public function createFromSecurity(): UserInterface;
}
