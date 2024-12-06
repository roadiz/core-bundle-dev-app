<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\Translation;

class AppFixtures extends Fixture
{
    public const DEFAULT_TRANSLATION_REFERENCE = 'default-translation';

    public function load(ObjectManager $manager): void
    {
        $defaultTranslation = new Translation();
        $defaultTranslation->setName('en');
        $defaultTranslation->setLocale('en');
        $defaultTranslation->setDefaultTranslation(true);
        $manager->persist($defaultTranslation);

        $manager->flush();
        $this->addReference(self::DEFAULT_TRANSLATION_REFERENCE, $defaultTranslation);
    }
}
