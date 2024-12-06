<?php

declare(strict_types=1);

namespace App\Entity;

use App\GeneratedEntity\NSPage;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractPositioned;
use RZ\Roadiz\CoreBundle\Entity\User;

#[
    ORM\Entity(),
    ORM\Table(name: 'positioned_page_user'),
    ORM\Index(columns: ['position'], name: 'ppu_position'),
    ORM\Index(columns: ['node_source_id', 'position'], name: 'ppu_node_source_id_position'),
]
class PositionedPageUser extends AbstractPositioned
{
    #[ORM\ManyToOne(targetEntity: '\App\GeneratedEntity\NSPage', inversedBy: 'usersProxy')]
    #[ORM\JoinColumn(name: 'node_source_id', onDelete: 'CASCADE')]
    private ?NSPage $nodeSource;

    #[ORM\ManyToOne(targetEntity: '\RZ\Roadiz\CoreBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'user_id', onDelete: 'CASCADE')]
    private ?User $user;

    public function getNodeSource(): ?NSPage
    {
        return $this->nodeSource;
    }

    public function setNodeSource(?NSPage $nodeSource): PositionedPageUser
    {
        $this->nodeSource = $nodeSource;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): PositionedPageUser
    {
        $this->user = $user;

        return $this;
    }
}
