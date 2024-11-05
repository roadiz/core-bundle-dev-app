<?php

declare(strict_types=1);

namespace Themes\Rozier\Explorer;

use RZ\Roadiz\CoreBundle\Entity\Setting;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SettingExplorerItem extends AbstractExplorerItem
{
    public function __construct(
        private readonly Setting $setting,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getId(): int|string
    {
        return $this->setting->getId() ?? throw new \RuntimeException('Entity must have an ID');
    }

    public function getAlternativeDisplayable(): ?string
    {
        if (null !== $this->setting->getSettingGroup()) {
            return $this->setting->getSettingGroup()->getName();
        }

        return '';
    }

    public function getDisplayable(): string
    {
        return $this->setting->getName();
    }

    public function getOriginal(): Setting
    {
        return $this->setting;
    }

    protected function getEditItemPath(): ?string
    {
        return $this->urlGenerator->generate('settingsEditPage', [
            'settingId' => $this->setting->getId(),
        ]);
    }
}
