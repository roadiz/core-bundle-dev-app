<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Model;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

final readonly class NodeDuplicateDto
{
    public function __construct(
        #[NotBlank]
        public string $csrfToken,
        #[Range(min: 1)]
        public int $nodeId,
    ) {
    }
}
