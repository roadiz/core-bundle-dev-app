<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Api\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class UserInput extends AbstractUserInput
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\NotCompromisedPassword()]
    public string $plainPassword = '';
}
