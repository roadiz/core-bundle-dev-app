<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Explorer;

use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem;
use RZ\Roadiz\Documents\Models\BaseDocumentInterface;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Uid\Uuid;

final class ConfigurableExplorerItem extends AbstractExplorerItem
{
    public function __construct(
        private readonly PersistableInterface $entity,
        private readonly array $configuration,
    ) {
    }

    #[\Override]
    public function getId(): string|int|Uuid
    {
        return $this->entity->getId() ?? throw new \RuntimeException('Entity must have an ID');
    }

    #[\Override]
    public function getAlternativeDisplayable(): string
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

    #[\Override]
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

    #[\Override]
    public function getOriginal(): PersistableInterface
    {
        return $this->entity;
    }

    #[\Override]
    protected function getThumbnail(): ?BaseDocumentInterface
    {
        /** @var BaseDocumentInterface|null $thumbnail */
        $thumbnail = null;
        if (!empty($this->configuration['thumbnail'])) {
            $thumbnailCallable = [$this->entity, $this->configuration['thumbnail']];
            if (\is_callable($thumbnailCallable)) {
                $thumbnail = call_user_func($thumbnailCallable);
                if ($thumbnail instanceof Collection && $thumbnail->count() > 0 && $thumbnail->first() instanceof BaseDocumentInterface) {
                    $thumbnail = $thumbnail->first();
                } elseif (is_array($thumbnail) && count($thumbnail) > 0 && $thumbnail[0] instanceof BaseDocumentInterface) {
                    $thumbnail = $thumbnail[0];
                }
            }
        }

        return $thumbnail;
    }

    #[\Override]
    protected function getEditItemPath(): ?string
    {
        return null;
    }
}
