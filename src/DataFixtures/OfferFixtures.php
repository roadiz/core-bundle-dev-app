<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\GeneratedEntity\NSOffer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Node\UniqueNodeGenerator;

class OfferFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UniqueNodeGenerator $uniqueNodeGenerator,
    ) {
    }

    public function getDependencies(): array
    {
        return [
            AppFixtures::class,
            NodeTypeFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $offerContainer = $this->uniqueNodeGenerator->generate(
            // @phpstan-ignore-next-line
            nodeType: $this->getReference(NodeTypeFixtures::NS_NEUTRAL, NodeType::class),
            // @phpstan-ignore-next-line
            translation: $this->getReference(AppFixtures::DEFAULT_TRANSLATION_REFERENCE, Translation::class),
            flush: false,
        );
        $offerContainer->setTitle('Offers container');
        $offerContainer->getNode()->setStatus(NodeStatus::PUBLISHED);
        $offerContainer->setPublishedAt(new \DateTime());

        for ($i = 0; $i < 50; ++$i) {
            /** @var NSOffer $offer */
            $offer = $this->uniqueNodeGenerator->generate(
                // @phpstan-ignore-next-line
                nodeType: $this->getReference(NodeTypeFixtures::NS_OFFER, NodeType::class),
                // @phpstan-ignore-next-line
                translation: $this->getReference(AppFixtures::DEFAULT_TRANSLATION_REFERENCE, Translation::class),
                parent: $offerContainer->getNode(),
                flush: false,
            );
            $offer->getNode()->setStatus(NodeStatus::PUBLISHED);
            $offer->setPrice(mt_rand(10, 10000));
            $offer->setVat(0.2);
            $offer->setPublishedAt(new \DateTime());
        }

        $manager->flush();
    }
}
