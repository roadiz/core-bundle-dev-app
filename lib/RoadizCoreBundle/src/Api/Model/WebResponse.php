<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

final class WebResponse implements WebResponseInterface, BlocksAwareWebResponseInterface, RealmsAwareWebResponseInterface
{
    use WebResponseTrait;
}
