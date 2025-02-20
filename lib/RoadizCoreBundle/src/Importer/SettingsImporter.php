<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Importer;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Setting;
use RZ\Roadiz\CoreBundle\Entity\SettingGroup;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class SettingsImporter implements EntityImporterInterface
{
    public function __construct(private ManagerRegistry $managerRegistry, private SerializerInterface $serializer)
    {
    }

    public function supports(string $entityClass): bool
    {
        return Setting::class === $entityClass;
    }

    public function import(string $serializedData): bool
    {
        $manager = $this->managerRegistry->getManagerForClass(Setting::class);
        $settings = $this->serializer->deserialize(
            $serializedData,
            Setting::class.'[]',
            'json',
            ['groups' => ['setting']]
        );

        foreach ($settings as $setting) {
            $this->importSingleSetting($setting);
        }

        $manager->flush();

        if ($manager instanceof EntityManagerInterface) {
            // Clear result cache
            $cacheDriver = $manager->getConfiguration()->getResultCacheImpl();
            if ($cacheDriver instanceof CacheProvider) {
                $cacheDriver->deleteAll();
            }
        }

        return true;
    }

    private function importSingleSetting(Setting $setting): void
    {
        $manager = $this->managerRegistry->getManagerForClass(Setting::class);
        $existingSetting = $this->managerRegistry->getRepository(Setting::class)->findOneByName($setting->getName());

        if (null !== $settingGroup = $setting->getSettingGroup()) {
            $existingSettingGroup = $this->managerRegistry->getRepository(SettingGroup::class)->findOneByName($settingGroup->getName());
            if (null === $existingSettingGroup) {
                $manager->persist($settingGroup);
                $manager->flush();
            } else {
                $existingSettingGroup->setName($settingGroup->getName());
                $existingSettingGroup->setInMenu($settingGroup->isInMenu());
                $setting->setSettingGroup($existingSettingGroup);
            }
        }

        if (null === $existingSetting) {
            $manager->persist($setting);

            return;
        }

        /*
         * Update existing setting
         */
        $existingSetting->setName($setting->getName());
        $existingSetting->setDefaultValues($setting->getDefaultValues());
        $existingSetting->setVisible($setting->isVisible());
        $existingSetting->setDescription($setting->getDescription());
        $existingSetting->setType($setting->getType());

        // Only override value when defined
        if (null !== $setting->getRawValue()) {
            $existingSetting->setValue($setting->getRawValue());
        }
    }
}
