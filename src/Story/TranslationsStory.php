<?php

declare(strict_types=1);

namespace App\Story;

use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Zenstruck\Foundry\Story;

final class TranslationsStory extends Story
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
    ) {
    }

    #[\Override]
    public function build(): void
    {
        $manager = $this->manager;

        $defaultTranslation = (new Translation())
            ->setName('en')
            ->setLocale('en')
            ->setAvailable(true)
            ->setDefaultTranslation(true);

        $frenchTranslation = (new Translation())
            ->setName('fr')
            ->setLocale('fr')
            ->setAvailable(true)
            ->setDefaultTranslation(false);

        $manager->persist($defaultTranslation);
        $manager->persist($frenchTranslation);
        $manager->flush();

        $this->addState('defaultTranslation', $defaultTranslation);
        $this->addState('frenchTranslation', $frenchTranslation);
    }
}
