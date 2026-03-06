<?php

declare(strict_types=1);

namespace App\Story;

use App\GeneratedEntity\NSOffer;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Node\UniqueNodeGenerator;
use Zenstruck\Foundry\Story;

use function Zenstruck\Foundry\faker;

final class OfferStory extends Story
{
    public function __construct(
        private readonly UniqueNodeGenerator $uniqueNodeGenerator,
        private readonly NodeTypes $nodeTypesBag,
    ) {
    }

    #[\Override]
    public function build(): void
    {
        $translation = TranslationsStory::get('defaultTranslation');
        if (!$translation instanceof Translation) {
            throw new \RuntimeException('Default translation story state is invalid.');
        }

        $homePage = PageHierarchyStory::get('homePage');
        if (!$homePage instanceof NodesSources) {
            throw new \RuntimeException('Home page story state is invalid.');
        }

        $offerType = $this->getNodeType('Offer');

        $container = $this->uniqueNodeGenerator->generate(
            nodeType: $offerType,
            translation: $translation,
            parent: $homePage->getNode(),
            flush: false,
        );
        $container->setTitle('Offers container');
        $container->setPublishedAt(new \DateTime('-2 days'));
        $container->getNode()->setStatus(NodeStatus::PUBLISHED);

        for ($i = 1; $i <= 20; ++$i) {
            $offer = $this->uniqueNodeGenerator->generate(
                nodeType: $offerType,
                translation: $translation,
                parent: $container->getNode(),
                flush: false,
            );
            $offer->setTitle('Offer '.$i);
            $offer->setPublishedAt(new \DateTime('-'.$i.' hours'));
            $offer->getNode()->setStatus(NodeStatus::PUBLISHED);

            if ($offer instanceof NSOffer) {
                $offer->setPrice(faker()->numberBetween(100, 10000));
                $offer->setVat(0.2);
                $offer->setLayout(0 === $i % 2 ? 'dark' : null);
                $offer->setGeolocation([
                    'lat' => faker()->latitude(42.0, 51.0),
                    'lng' => faker()->longitude(-5.0, 8.0),
                ]);
            }

            $this->addToPool('offers', $offer);
        }

        $this->addState('offerContainer', $container);
    }

    private function getNodeType(string $name): NodeType
    {
        return $this->nodeTypesBag->get($name) ?? throw new \RuntimeException(sprintf('%s node type is missing.', $name));
    }
}
