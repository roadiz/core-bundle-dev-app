<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Model;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final readonly class TagCreationDto
{
    public function __construct(
        #[NotBlank]
        public string $csrfToken,
        #[Length(min: 3, max: 200)]
        #[NotBlank]
        public string $tagName,
    ) {
    }
}
