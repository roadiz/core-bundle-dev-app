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
        // genId: false, // https://github.com/api-platform/core/issues/7162
    )]
    public ?array $menus = null;
}
