<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Security\Provider;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\TwoFactorBundle\Entity\TwoFactorUser;

interface TwoFactorUserProviderInterface
{
    public function getFromUser(User $user): ?TwoFactorUser;
}
