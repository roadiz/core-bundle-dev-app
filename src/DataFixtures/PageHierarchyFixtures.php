<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Node\UniqueNodeGenerator;

class PageHierarchyFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UniqueNodeGenerator $uniqueNodeGenerator,
        private readonly NodeTypes $nodeTypesBag,
    ) {
    }

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
        $parent = null;
        for ($i = 1; $i <= 5; ++$i) {
            $page = $this->uniqueNodeGenerator->generate(
                // @phpstan-ignore-next-line
                nodeType: $this->nodeTypesBag->get('Page'),
                // @phpstan-ignore-next-line
                translation: $this->getReference(AppFixtures::DEFAULT_TRANSLATION_REFERENCE, Translation::class),
                parent: $parent?->getNode(),
                flush: false,
            );
            $parent = $page;

            $page->setTitle('Page level '.$i);
            $page->getNode()->setStatus(NodeStatus::PUBLISHED);
            $page->setPublishedAt(new \DateTime());
        }
        $manager->flush();
    }
}
