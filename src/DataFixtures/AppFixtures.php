<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Story\TranslationsStory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\Translation;

class AppFixtures extends Fixture
{
    public const string DEFAULT_TRANSLATION_REFERENCE = 'default-translation';

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $defaultTranslation = TranslationsStory::get('defaultTranslation');
        if (!$defaultTranslation instanceof Translation) {
            throw new \RuntimeException('Default translation story state is invalid.');
        }

        $this->addReference(self::DEFAULT_TRANSLATION_REFERENCE, $defaultTranslation);
    }
}
