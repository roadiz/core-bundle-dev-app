<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Api\Breadcrumbs\BreadcrumbsInterface;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\TreeWalker\WalkerInterface;
use Symfony\Component\Serializer\Attribute as Serializer;

trait WebResponseTrait
{
    #[ApiProperty(
        description: 'The path of the current WebResponse.',
        readable: true,
        writable: false,
        identifier: true
    )]
    public ?string $path = null;

    #[Serializer\Groups(['web_response'])]
    public ?PersistableInterface $item = null;

    #[Serializer\Groups(['web_response'])]
    #[ApiProperty(
        identifier: false,
        // genId: false, // https://github.com/api-platform/core/issues/7162
    )]
    public ?BreadcrumbsInterface $breadcrumbs = null;

    #[Serializer\Groups(['web_response'])]
    #[ApiProperty(
        identifier: false,
        // genId: false, // https://github.com/api-platform/core/issues/7162
    )]
    public ?NodesSourcesHeadInterface $head = null;
    /**
     * @var Collection<int, WalkerInterface>|null
     */
    #[Serializer\Groups(['web_response'])]
    #[ApiProperty(
        identifier: false,
        // genId: false, // https://github.com/api-platform/core/issues/7162
    )]
    private ?Collection $blocks = null;
    /**
     * @var array<RealmInterface>|null
     */
    #[Serializer\Groups(['web_response'])]
    private ?array $realms = null;

    #[Serializer\Groups(['web_response'])]
    private bool $hidingBlocks = false;

    /**
     * @var int|null WebResponse item maximum age in seconds
     */
    #[Serializer\Groups(['web_response'])]
    private ?int $maxAge = null;

    public function getMaxAge(): ?int
    {
        return $this->maxAge;
    }

    public function setMaxAge(?int $maxAge): self
    {
        $this->maxAge = $maxAge;

        return $this;
    }

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

    public function getItem(): ?PersistableInterface
    {
        return $this->item;
    }

    /**
     * @return WalkerInterface[]|null
     */
    public function getBlocks(): ?array
    {
        return $this->blocks?->getValues();
    }

    /**
     * @param Collection<int, WalkerInterface>|null $blocks
     *
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
     *
     * @return $this
     */
    public function setRealms(?array $realms): self
    {
        $this->realms = $realms;

        return $this;
    }

    public function isHidingBlocks(): bool
    {
        return $this->hidingBlocks;
    }

    /**
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
