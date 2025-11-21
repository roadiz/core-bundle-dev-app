<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

final readonly class NodeCreationDto
{
    public function __construct(
        #[NotBlank]
        public string $csrfToken,
        #[NotBlank]
        public string $nodeTypeName,
        #[Range(min: 1)]
        public ?int $parentNodeId,
        #[Range(min: 1)]
        public int $translationId,
        #[Range(min: 1)]
        public ?int $tagId,
        public bool $pushTop = false,
    ) {
    }
}
