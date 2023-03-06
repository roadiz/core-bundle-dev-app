<?php

declare(strict_types=1);

namespace Themes\Rozier\Explorer;

use RZ\Roadiz\CoreBundle\Entity\Setting;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SettingExplorerItem extends AbstractExplorerItem
{
    private Setting $setting;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(Setting $setting, UrlGeneratorInterface $urlGenerator)
    {
        $this->setting = $setting;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritDoc
     */
    public function getId(): int|string
    {
        return $this->setting->getId() ?? throw new \RuntimeException('Entity must have an ID');
    }

    /**
     * @inheritDoc
     */
    public function getAlternativeDisplayable(): ?string
    {
        if (null !== $this->setting->getSettingGroup()) {
            return $this->setting->getSettingGroup()->getName();
        }
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getDisplayable(): string
    {
        return $this->setting->getName();
    }

    /**
     * @inheritDoc
     */
    public function getOriginal(): Setting
    {
        return $this->setting;
    }

    protected function getEditItemPath(): ?string
    {
        return $this->urlGenerator->generate('settingsEditPage', [
            'settingId' => $this->setting->getId()
        ]);
    }
}
