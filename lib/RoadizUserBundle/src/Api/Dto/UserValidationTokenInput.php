<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Api\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class UserValidationTokenInput
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $token = '';
}
