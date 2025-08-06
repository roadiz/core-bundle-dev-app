<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/*
 * Allow other bundles to add actions to Roadiz user panel.
 */
final class UserActionsMenuEvent extends Event
{
    private array $actions = [];

    public function addAction(string $label, string $path, string $icon): void
    {
        $this->actions[] = [
            'label' => $label,
            'path' => $path,
            'icon' => $icon,
        ];
    }

    public function getActions(): array
    {
        return $this->actions;
    }
}
