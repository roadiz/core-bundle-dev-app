<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Model;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

final readonly class NodeDuplicateDto
{
    public function __construct(
        #[Range(min: 1)]
        public int $nodeId,
        #[NotBlank]
        public string $csrfToken,
    ) {
    }
}
