<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Model;

use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

final readonly class NodeStatusDto
{
    public function __construct(
        #[NotBlank]
        public string $csrfToken,
        #[Range(min: 1)]
        public int $nodeId,
        #[Choice(choices: ['status', 'visible', 'locked', 'hideChildren'])]
        public string $statusName,
        public string|bool $statusValue,
    ) {
    }
}
