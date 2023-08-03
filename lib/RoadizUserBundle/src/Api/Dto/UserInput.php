<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Api\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class UserInput
{
    #[Assert\Email]
    #[Assert\NotNull]
    public string $email = '';

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\NotCompromisedPassword()]
    public string $plainPassword = '';
    public ?string $publicName = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $phone = null;
    public ?string $company = null;
    public ?string $job = null;
    public ?\DateTime $birthday = null;
    public ?array $metadata = null;
}
