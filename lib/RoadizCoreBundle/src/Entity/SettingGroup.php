<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\SequentialIdTrait;
use RZ\Roadiz\CoreBundle\Repository\SettingGroupRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Settings entity are a simple key-value configuration system.
 */
#[
    ORM\Entity(repositoryClass: SettingGroupRepository::class),
    ORM\Table(name: 'settings_groups'),
    UniqueEntity(fields: ['name'])
]
class SettingGroup implements PersistableInterface, \Stringable
{
    use SequentialIdTrait;

    #[ORM\Column(name: 'in_menu', type: 'boolean', nullable: false, options: ['default' => false])]
    #[Serializer\Groups(['setting', 'setting_group'])]
    protected bool $inMenu = false;

    #[ORM\Column(type: 'string', length: 250, unique: true)]
    #[Serializer\Groups(['setting', 'setting_group'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    private string $name = '';

    /**
     * @var Collection<int, Setting>
     */
    #[ORM\OneToMany(mappedBy: 'settingGroup', targetEntity: Setting::class)]
    #[Serializer\Groups(['setting_group'])]
    private Collection $settings;

    public function __construct()
    {
        $this->settings = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return SettingGroup
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function isInMenu(): bool
    {
        return $this->inMenu;
    }

    /**
     * @return SettingGroup
     */
    public function setInMenu(bool $newinMenu)
    {
        $this->inMenu = $newinMenu;

        return $this;
    }

    /**
     * @return SettingGroup
     */
    public function addSetting(Setting $setting)
    {
        if (!$this->getSettings()->contains($setting)) {
            $this->settings->add($setting);
        }

        return $this;
    }

    /**
     * @return Collection<int, Setting>
     */
    public function getSettings(): Collection
    {
        return $this->settings;
    }

    /**
     * @param Collection<int, Setting> $settings
     *
     * @return SettingGroup
     */
    public function addSettings(Collection $settings)
    {
        foreach ($settings as $setting) {
            if (!$this->getSettings()->contains($setting)) {
                $this->settings->add($setting);
            }
        }

        return $this;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getName();
    }
}
