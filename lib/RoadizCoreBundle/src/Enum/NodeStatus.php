<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum NodeStatus: int implements TranslatableInterface
{
    case DRAFT = 10;
    case PENDING = 20;
    case PUBLISHED = 30;
    case ARCHIVED = 40;
    case DELETED = 50;

    public function getLabel(): string
    {
        return match ($this) {
            NodeStatus::DRAFT => 'draft',
            NodeStatus::PENDING => 'pending',
            NodeStatus::PUBLISHED => 'published',
            NodeStatus::ARCHIVED => 'archived',
            NodeStatus::DELETED => 'deleted',
        };
    }

    public static function fromLabel(string $label): self
    {
        return match (strtolower($label)) {
            'pending' => NodeStatus::PENDING,
            'published' => NodeStatus::PUBLISHED,
            'archived' => NodeStatus::ARCHIVED,
            'deleted' => NodeStatus::DELETED,
            default => NodeStatus::DRAFT,
        };
    }

    public static function fromName(string $name): self
    {
        return match (strtoupper($name)) {
            'PENDING' => NodeStatus::PENDING,
            'PUBLISHED' => NodeStatus::PUBLISHED,
            'ARCHIVED' => NodeStatus::ARCHIVED,
            'DELETED' => NodeStatus::DELETED,
            default => NodeStatus::DRAFT,
        };
    }

    public static function allLabelsAndValues(): array
    {
        return array_combine(
            array_map(fn (NodeStatus $status) => $status->getLabel(), self::cases()),
            array_map(fn (NodeStatus $status) => $status->value, self::cases())
        );
    }

    public function isPublished(): bool
    {
        return NodeStatus::PUBLISHED === $this;
    }

    public function isPending(): bool
    {
        return NodeStatus::PENDING === $this;
    }

    public function isDraft(): bool
    {
        return NodeStatus::DRAFT === $this;
    }

    public function isDeleted(): bool
    {
        return NodeStatus::DELETED === $this;
    }

    public function isArchived(): bool
    {
        return NodeStatus::ARCHIVED === $this;
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->getLabel(), locale: $locale);
    }
}
