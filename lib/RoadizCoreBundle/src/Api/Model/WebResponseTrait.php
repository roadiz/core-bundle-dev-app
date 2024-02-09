<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Api\Breadcrumbs\BreadcrumbsInterface;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\TreeWalker\WalkerInterface;
use Symfony\Component\Serializer\Annotation as Serializer;

trait WebResponseTrait
{
    #[ApiProperty(identifier: true)]
    public ?string $path = null;

    #[Serializer\Groups(["web_response"])]
    public ?PersistableInterface $item = null;

    #[Serializer\Groups(["web_response"])]
    public ?BreadcrumbsInterface $breadcrumbs = null;

    #[Serializer\Groups(["web_response"])]
    public ?NodesSourcesHeadInterface $head = null;
    /**
     * @var Collection<int, WalkerInterface>|null
     */
    #[Serializer\Groups(["web_response"])]
    private ?Collection $blocks = null;
    /**
     * @var array<RealmInterface>|null
     */
    #[Serializer\Groups(["web_response"])]
    private ?array $realms = null;

    #[Serializer\Groups(["web_response"])]
    private bool $hidingBlocks = false;

    public function setPath(?string $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function setItem(?PersistableInterface $item): self
    {
        $this->item = $item;
        return $this;
    }

    /**
     * @return PersistableInterface|null
     */
    public function getItem(): ?PersistableInterface
    {
        return $this->item;
    }

    /**
     * @return Collection<int, WalkerInterface>|null
     */
    public function getBlocks(): ?Collection
    {
        return $this->blocks;
    }

    /**
     * @param Collection<int, WalkerInterface>|null $blocks
     * @return $this
     */
    public function setBlocks(?Collection $blocks): self
    {
        $this->blocks = $blocks;
        return $this;
    }

    /**
     * @return RealmInterface[]|null
     */
    public function getRealms(): ?array
    {
        return $this->realms;
    }

    /**
     * @param RealmInterface[]|null $realms
     * @return $this
     */
    public function setRealms(?array $realms): self
    {
        $this->realms = $realms;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHidingBlocks(): bool
    {
        return $this->hidingBlocks;
    }

    /**
     * @param bool $hidingBlocks
     * @return $this
     */
    public function setHidingBlocks(bool $hidingBlocks): self
    {
        $this->hidingBlocks = $hidingBlocks;
        return $this;
    }

    public function setBreadcrumbs(?BreadcrumbsInterface $breadcrumbs): self
    {
        $this->breadcrumbs = $breadcrumbs;
        return $this;
    }

    public function setHead(?NodesSourcesHeadInterface $head): self
    {
        $this->head = $head;
        return $this;
    }
}
