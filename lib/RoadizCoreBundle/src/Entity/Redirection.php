<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractDateTimed;
use RZ\Roadiz\CoreBundle\Repository\RedirectionRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Http redirection which are editable by BO users.
 */
#[
    ORM\Entity(repositoryClass: RedirectionRepository::class),
    ORM\Table(name: "redirections"),
    ORM\HasLifecycleCallbacks,
    UniqueEntity(fields: ["query"])
]
class Redirection extends AbstractDateTimed
{
    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $query = "";

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'redirectUri', type: 'text', length: 2048, nullable: true)]
    #[Assert\Length(max: 2048)]
    private ?string $redirectUri = null;

    /**
     * @var NodesSources|null
     */
    #[ORM\ManyToOne(targetEntity: NodesSources::class, cascade: ['persist'], inversedBy: 'redirections')]
    #[ORM\JoinColumn(name: 'ns_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?NodesSources $redirectNodeSource = null;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    private int $type = 301;

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param string $query
     * @return Redirection
     */
    public function setQuery($query): Redirection
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRedirectUri(): ?string
    {
        return $this->redirectUri;
    }

    /**
     * @param string|null $redirectUri
     * @return Redirection
     */
    public function setRedirectUri($redirectUri): Redirection
    {
        $this->redirectUri = $redirectUri;
        return $this;
    }

    /**
     * @return NodesSources|null
     */
    public function getRedirectNodeSource(): ?NodesSources
    {
        return $this->redirectNodeSource;
    }

    /**
     * @param NodesSources|null $redirectNodeSource
     * @return Redirection
     */
    public function setRedirectNodeSource(NodesSources $redirectNodeSource = null): Redirection
    {
        $this->redirectNodeSource = $redirectNodeSource;
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTypeAsString(): string
    {
        $types = [
            Response::HTTP_MOVED_PERMANENTLY => 'redirection.moved_permanently',
            Response::HTTP_FOUND => 'redirection.moved_temporarily',
        ];

        return $types[$this->type] ?? '';
    }

    /**
     * @param int $type
     * @return Redirection
     */
    public function setType(int $type): Redirection
    {
        $this->type = $type;
        return $this;
    }

    public function __construct()
    {
        $this->type = Response::HTTP_MOVED_PERMANENTLY;
        $this->initAbstractDateTimed();
    }
}
