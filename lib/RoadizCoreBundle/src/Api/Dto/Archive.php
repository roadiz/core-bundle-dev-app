<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(operations: [])]
final readonly class Archive
{
    public function __construct(
        #[Groups(['archives'])]
        #[ApiProperty(description: 'Archive year', example: 2022)]
        public int|string $year,
        #[Groups(['archives'])]
        #[ApiProperty(description: 'Archive months with YYYY-MM as keys and ISO date-time as value', example: [
            '2022-06' => '2022-06-01T00:00:00+02:00',
            '2022-05' => '2022-05-01T00:00:00+02:00',
        ])]
        public array $months,
    ) {
    }
}
