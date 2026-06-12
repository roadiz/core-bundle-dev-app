<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Story\TagStory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TagFixtures extends Fixture implements DependentFixtureInterface
{
    #[\Override]
    public function getDependencies(): array
    {
        return [
            ArticleFixtures::class,
        ];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        TagStory::load();
    }
}
