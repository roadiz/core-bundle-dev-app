<?php

declare(strict_types=1);

namespace App\Api\Model;

use ApiPlatform\Metadata\ApiProperty;
use RZ\Roadiz\CoreBundle\Api\Model\NodesSourcesHeadInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Component\Serializer\Annotation\Groups;

final class CommonContent
{
    #[ApiProperty(identifier: true)]
    public string $id = 'unique';

    #[Groups(['common_content'])]
    public ?NodesSources $home = null;

    #[Groups(['common_content'])]
    #[ApiProperty(
        identifier: false,
        // genId: false, // https://github.com/api-platform/core/issues/7162
    )]
    public ?NodesSourcesHeadInterface $head = null;

    #[Groups(['common_content'])]
    #[ApiProperty(
        identifier: false,
        openapiContext: [
            'description' => 'List of the website menus.',
        ],
        // genId: false, // https://github.com/api-platform/core/issues/7162
    )]
    public ?array $menus = null;

    #[Groups(['common_content'])]
    #[ApiProperty(
        identifier: false,
        openapiContext: [
            'description' => 'List of global external URLs for the website.',
            'example' => [
                'first_url' => 'https://example.com',
                'second_url' => 'https://another-example.com',
            ],
        ],
        // genId: false, // https://github.com/api-platform/core/issues/7162
    )]
    public ?array $urls = null;

    #[Groups(['common_content'])]
    #[ApiProperty(
        identifier: false,
        openapiContext: [
            'description' => 'List of global colors for the website.',
            'example' => [
                'first_color' => '#00ff00',
                'second_color' => '#ff0000',
            ],
        ],
        // genId: false, // https://github.com/api-platform/core/issues/7162
    )]
    public ?array $colors = null;
}
