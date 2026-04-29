<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Story\OfferStory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OfferFixtures extends Fixture implements DependentFixtureInterface
{
    #[\Override]
    public function getDependencies(): array
    {
        return [
            PageHierarchyFixtures::class,
        ];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        OfferStory::load();
    }
}
