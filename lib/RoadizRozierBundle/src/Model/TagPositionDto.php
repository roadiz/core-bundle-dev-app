<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Model;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

final readonly class TagPositionDto
{
    public function __construct(
        #[Range(min: 1)]
        #[NotBlank]
        public int $tagId,
        #[NotBlank]
        public string $csrfToken,
        #[Range(min: 1)]
        public ?int $nextTagId = null,
        #[Range(min: 1)]
        public ?int $prevTagId = null,
        public bool $firstPosition = false,
        public bool $lastPosition = false,
        #[Range(min: 1)]
        public ?int $newParentTagId = null,
    ) {
    }
}
