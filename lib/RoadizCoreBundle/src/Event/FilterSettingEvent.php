<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use RZ\Roadiz\CoreBundle\Entity\Setting;
use Symfony\Contracts\EventDispatcher\Event;

abstract class FilterSettingEvent extends Event
{
    public function __construct(protected readonly Setting $setting)
    {
    }

    public function getSetting(): Setting
    {
        return $this->setting;
    }
}
