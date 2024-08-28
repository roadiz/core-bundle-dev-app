<?php

declare(strict_types=1);

namespace Themes\Rozier\Explorer;

use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\String\UnicodeString;

final class ConfigurableExplorerItem extends AbstractExplorerItem
{
    public function __construct(
        private readonly PersistableInterface $entity,
        private readonly array $configuration,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getId(): int|string
    {
        return $this->entity->getId() ?? throw new \RuntimeException('Entity must have an ID');
    }

    /**
     * @inheritDoc
     */
    public function getAlternativeDisplayable(): ?string
    {
        $alt = $this->configuration['classname'];
        if (!empty($this->configuration['alt_displayable'])) {
            $altDisplayableCallable = [$this->entity, $this->configuration['alt_displayable']];
            if (\is_callable($altDisplayableCallable)) {
                $alt = call_user_func($altDisplayableCallable);
                if ($alt instanceof \DateTimeInterface) {
                    $alt = $alt->format('c');
                }
            }
        }
        return (new UnicodeString($alt ?? ''))->truncate(30, '…')->toString();
    }

    /**
     * @inheritDoc
     */
    public function getDisplayable(): string
    {
        $displayableCallable = [$this->entity, $this->configuration['displayable']];
        if (\is_callable($displayableCallable)) {
            $displayable = call_user_func($displayableCallable);
            if ($displayable instanceof \DateTimeInterface) {
                $displayable = $displayable->format('c');
            }
        }
        return (new UnicodeString($displayable ?? ''))->truncate(30, '…')->toString();
    }

    /**
     * @inheritDoc
     */
    public function getOriginal(): PersistableInterface
    {
        return $this->entity;
    }

    protected function getThumbnail(): ?DocumentInterface
    {
        /** @var DocumentInterface|null $thumbnail */
        $thumbnail = null;
        if (!empty($this->configuration['thumbnail'])) {
            $thumbnailCallable = [$this->entity, $this->configuration['thumbnail']];
            if (\is_callable($thumbnailCallable)) {
                $thumbnail = call_user_func($thumbnailCallable);
                if ($thumbnail instanceof Collection && $thumbnail->count() > 0 && $thumbnail->first() instanceof DocumentInterface) {
                    $thumbnail = $thumbnail->first();
                } elseif (is_array($thumbnail) && count($thumbnail) > 0 && $thumbnail[0] instanceof DocumentInterface) {
                    $thumbnail = $thumbnail[0];
                }
            }
        }

        return $thumbnail;
    }

    protected function getEditItemPath(): ?string
    {
        return null;
    }
}
