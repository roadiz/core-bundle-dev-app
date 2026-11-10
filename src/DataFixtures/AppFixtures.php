<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Story\AbstractNodeStory;
use App\Story\AppStory;
use App\Story\TranslationsStory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\Translation;

/**
 * Single Doctrine entry point: AppStory owns load order and Faker seed.
 */
class AppFixtures extends Fixture
{
    public const string DEFAULT_TRANSLATION_REFERENCE = 'default-translation';

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        AppStory::load();

        $this->addReference(
            self::DEFAULT_TRANSLATION_REFERENCE,
            AbstractNodeStory::expect(TranslationsStory::get('defaultTranslation'), Translation::class),
        );
    }
}
