<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use ApiPlatform\Metadata\ApiResource;

final class WebResponse implements WebResponseInterface, BlocksAwareWebResponseInterface, RealmsAwareWebResponseInterface
{
    use WebResponseTrait;
}
