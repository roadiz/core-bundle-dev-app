<?php

declare(strict_types=1);

namespace RZ\Roadiz\OpenId\Authentication\Provider;

use Symfony\Component\HttpFoundation\ParameterBag;

class SettingsRoleStrategy implements JwtRoleStrategy
{
    public const SETTING_NAME = 'openid_default_roles';

    public function __construct(
        protected readonly ParameterBag $settingsBag
    ) {
    }

    public function supports(): bool
    {
        return !empty($this->settingsBag->get(static::SETTING_NAME));
    }

    public function getRoles(): ?array
    {
        $settings = $this->settingsBag->get(static::SETTING_NAME);
        if (!is_string($settings)) {
            return null;
        }
        return array_map(function ($role) {
            return trim($role);
        }, explode(',', $settings));
    }
}
