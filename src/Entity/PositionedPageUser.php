<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractPositioned;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\User;

#[
    ORM\Entity(),
    ORM\Table(name: "positioned_page_user"),
    ORM\Index(columns: ["position"], name: "ppu_position"),
    ORM\Index(columns: ["node_source_id", "position"], name: "ppu_node_source_id_position"),
]
class PositionedPageUser extends AbstractPositioned
{
    /**
     * @var NodesSources|null
     */
    #[ORM\ManyToOne(targetEntity: '\App\GeneratedEntity\NSPage', inversedBy: 'usersProxy')]
    #[ORM\JoinColumn(name: 'node_source_id', onDelete: 'CASCADE')]
    private ?NodesSources $nodeSource;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: '\RZ\Roadiz\CoreBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'user_id', onDelete: 'CASCADE')]
    private ?User $user;

    /**
     * @return NodesSources|null
     */
    public function getNodeSource(): ?NodesSources
    {
        return $this->nodeSource;
    }

    /**
     * @param NodesSources|null $nodeSource
     * @return PositionedPageUser
     */
    public function setNodeSource(?NodesSources $nodeSource): PositionedPageUser
    {
        $this->nodeSource = $nodeSource;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return PositionedPageUser
     */
    public function setUser(?User $user): PositionedPageUser
    {
        $this->user = $user;
        return $this;
    }
}
