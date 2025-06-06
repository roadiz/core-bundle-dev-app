<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Security;

use RZ\Roadiz\CoreBundle\Security\Authentication\RoadizAuthenticator;
use Symfony\Component\HttpFoundation\Request;

class RozierAuthenticator extends RoadizAuthenticator
{
    #[\Override]
    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('loginPage');
    }

    #[\Override]
    protected function getDefaultSuccessPath(Request $request): string
    {
        return $this->urlGenerator->generate('adminHomePage');
    }
}
