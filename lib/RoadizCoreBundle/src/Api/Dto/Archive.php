<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

final class Archive
{
    #[Groups(['archives'])]
    public int $year;

    #[Groups(['archives'])]
    public array $months;
}
