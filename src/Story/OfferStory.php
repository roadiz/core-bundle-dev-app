<?php

declare(strict_types=1);

namespace App\Story;

use App\GeneratedEntity\NSOffer;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;

use function Zenstruck\Foundry\faker;

final class OfferStory extends AbstractNodeStory
{
    #[\Override]
    public function build(): void
    {
        $homePage = self::expect(PageHierarchyStory::get('homePage'), NodesSources::class);

        $container = $this->publishedNode('Offer', NSOffer::class, 'Offers container', $homePage->getNode(), '-2 days');

        for ($i = 1; $i <= 20; ++$i) {
            $offer = $this->publishedNode('Offer', NSOffer::class, 'Offer '.$i, $container->getNode(), '-'.$i.' hours');
            $offer->setPrice(faker()->numberBetween(100, 10000));
            $offer->setVat(0.2);
            $offer->setLayout(0 === $i % 2 ? 'dark' : null);
            $offer->setGeolocation([
                'lat' => faker()->latitude(42.0, 51.0),
                'lng' => faker()->longitude(-5.0, 8.0),
            ]);
        }

        $this->entityManager->flush();
    }
}
