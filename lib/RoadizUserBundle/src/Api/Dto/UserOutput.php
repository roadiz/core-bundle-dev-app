<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Api\Dto;

use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [],
)]
final class UserOutput
{
    #[Groups(['user'])]
    public string $identifier = '';
    #[Groups(['user'])]
    public array $roles = [];
    #[Groups(['user'])]
    public ?string $firstName = null;
    #[Groups(['user'])]
    public ?string $publicName = null;
    #[Groups(['user'])]
    public ?string $lastName = null;
    #[Groups(['user'])]
    public ?string $phone = null;
    #[Groups(['user'])]
    public ?string $company = null;
    #[Groups(['user'])]
    public ?string $locale = null;
    #[Groups(['user'])]
    public ?string $pictureUrl = null;
    #[Groups(['user'])]
    public ?array $metadata = null;
    #[Groups(['user'])]
    public ?string $job = null;
    #[Groups(['user'])]
    public ?\DateTime $birthday = null;
    #[Groups(['user'])]
    public bool $emailValidated = false;
}
