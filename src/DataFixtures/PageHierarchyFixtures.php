<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Story\PageHierarchyStory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PageHierarchyFixtures extends Fixture implements DependentFixtureInterface
{
    #[\Override]
    public function getDependencies(): array
    {
        return [
            AppFixtures::class,
        ];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        PageHierarchyStory::load();
    }
}
