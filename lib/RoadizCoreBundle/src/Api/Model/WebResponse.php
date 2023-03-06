<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Api\Breadcrumbs\BreadcrumbsInterface;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\TreeWalker\WalkerInterface;
use Symfony\Component\Serializer\Annotation as Serializer;

final class WebResponse implements WebResponseInterface, BlocksAwareWebResponseInterface, RealmsAwareWebResponseInterface
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
     * @var Collection<WalkerInterface>|null
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

    /**
     * @return PersistableInterface|null
     */
    public function getItem(): ?PersistableInterface
    {
        return $this->item;
    }

    /**
     * @return Collection<WalkerInterface>|null
     */
    public function getBlocks(): ?Collection
    {
        return $this->blocks;
    }

    /**
     * @param Collection<WalkerInterface>|null $blocks
     * @return WebResponse
     */
    public function setBlocks(?Collection $blocks): WebResponse
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
     * @return WebResponse
     */
    public function setRealms(?array $realms): WebResponse
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
     * @return WebResponse
     */
    public function setHidingBlocks(bool $hidingBlocks): WebResponse
    {
        $this->hidingBlocks = $hidingBlocks;
        return $this;
    }
}
