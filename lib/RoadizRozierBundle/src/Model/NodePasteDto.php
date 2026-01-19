<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Model;

use Symfony\Component\Validator\Constraints\Expression;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

#[Expression(
    expression: 'null !== this.parentNodeId or null !== this.prevNodeId',
    message: 'NodePasteDto requires at least a parentNodeId or a prevNodeId to determine the paste position.',
)]
final readonly class NodePasteDto
{
    public function __construct(
        #[NotBlank]
        public string $csrfToken,
        #[Range(min: 1)]
        public int $nodeId,
        #[Range(min: 1)]
        public ?int $parentNodeId = null,
        #[Range(min: 1)]
        public ?int $prevNodeId = null,
    ) {
    }
}
