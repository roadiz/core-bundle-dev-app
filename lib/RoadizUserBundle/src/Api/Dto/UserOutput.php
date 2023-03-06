<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Api\Dto;

final class UserOutput
{
    public string $identifier = '';
    public array $roles = [];
    public ?string $firstName = null;
    public ?string $publicName = null;
    public ?string $lastName = null;
    public ?string $phone = null;
    public ?string $company = null;
    public ?string $locale = null;
    public ?string $pictureUrl = null;
    public ?array $metadata = null;
    public ?string $job = null;
    public ?\DateTime $birthday = null;
    public bool $emailValidated = false;
}
