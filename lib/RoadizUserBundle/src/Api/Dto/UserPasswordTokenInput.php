<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Api\Dto;

final class UserPasswordTokenInput
{
    public string $token = '';
    public string $plainPassword = '';
}
