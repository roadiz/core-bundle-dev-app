<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\LoginLink;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;

interface LoginLinkSenderInterface
{
    public function sendLoginLink(
        UserInterface $user,
        LoginLinkDetails $loginLinkDetails,
    ): void;
}
