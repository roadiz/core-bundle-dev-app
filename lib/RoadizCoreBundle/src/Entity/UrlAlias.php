<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\CoreBundle\Repository\UrlAliasRepository;
use RZ\Roadiz\Utils\StringHandler;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;
use RZ\Roadiz\CoreBundle\Form\Constraint as RoadizAssert;

/**
 * UrlAliases are used to translate Nodes URLs.
 */
#[
    ORM\Entity(repositoryClass: UrlAliasRepository::class),
    ORM\Table(name: "url_aliases")
]
class UrlAlias extends AbstractEntity
{
    #[ORM\Column(type: 'string', length: 250, unique: true)]
    #[SymfonySerializer\Groups(['url_alias'])]
    #[Serializer\Groups(['url_alias'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    #[RoadizAssert\UniqueNodeName]
    private string $alias = '';

    #[ORM\ManyToOne(targetEntity: NodesSources::class, inversedBy: 'urlAliases')]
    #[ORM\JoinColumn(name: 'ns_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private NodesSources $nodeSource;

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     *
     * @return $this
     */
    public function setAlias(string $alias): UrlAlias
    {
        $this->alias = StringHandler::slugify($alias);
        return $this;
    }

    public function getNodeSource(): NodesSources
    {
        return $this->nodeSource;
    }

    public function setNodeSource(NodesSources $nodeSource): UrlAlias
    {
        $this->nodeSource = $nodeSource;
        return $this;
    }
}
