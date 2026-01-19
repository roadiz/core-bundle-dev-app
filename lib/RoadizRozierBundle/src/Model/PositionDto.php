<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Model;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

final readonly class PositionDto
{
    public function __construct(
        #[NotBlank]
        public string $csrfToken,
        #[Range(min: 1)]
        #[NotBlank]
        public int $id,
        #[Range(min: 1)]
        public ?int $nextId = null,
        #[Range(min: 1)]
        public ?int $prevId = null,
        public bool $firstPosition = false,
        public bool $lastPosition = false,
        #[Range(min: 1)]
        public ?int $newParentId = null,
    ) {
    }
}
