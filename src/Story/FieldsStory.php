<?php

declare(strict_types=1);

namespace App\Story;

use App\GeneratedEntity\NSFields;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;

use function Zenstruck\Foundry\faker;

final class FieldsStory extends AbstractNodeStory
{
    #[\Override]
    public function build(): void
    {
        $homePage = self::expect(PageHierarchyStory::get('homePage'), NodesSources::class);
        $pageLevel2 = self::expect(PageHierarchyStory::get('pageLevel2'), NodesSources::class);

        $fields = $this->publishedNode('Fields', NSFields::class, 'Fields test node', $homePage->getNode(), '-1 day');
        $fields->setSubTitle('All field types coverage');
        $fields->setLongText(faker()->paragraph(3));
        $fields->setContent(faker()->text(400));
        $fields->setColor('#00FF00');
        $fields->setSticky(true);
        $fields->setStickytest(false);
        $fields->setAmount((string) faker()->randomFloat(3, 100, 9999));
        $fields->setPrice(faker()->numberBetween(100, 10000));
        $fields->setEmailTest(faker()->safeEmail());
        $fields->setSettings([
            'feature_flag' => true,
            'section' => faker()->slug(2),
        ]);
        $fields->setContacts([
            ['name' => faker()->name(), 'email' => faker()->safeEmail()],
        ]);
        $fields->setFolder('default-folder');
        $fields->setCountry('FR');
        $fields->setGeolocation([
            'lat' => faker()->latitude(42.0, 51.0),
            'lng' => faker()->longitude(-5.0, 8.0),
        ]);
        $fields->setMultiGeolocation([
            ['lat' => faker()->latitude(42.0, 51.0), 'lng' => faker()->longitude(-5.0, 8.0)],
            ['lat' => faker()->latitude(42.0, 51.0), 'lng' => faker()->longitude(-5.0, 8.0)],
        ]);
        $fields->setLayout('dark');
        $fields->setDate(faker()->dateTimeBetween('-2 years', '-1 year'));
        $fields->setDatetime(faker()->dateTimeBetween('-1 year', 'now'));
        $fields->setCss('body { color: #111; }');
        $fields->setYaml('k1: '.faker()->word()."\nk2:\n  - ".faker()->word());
        $fields->setJson((string) json_encode([
            'enabled' => true,
            'max' => faker()->numberBetween(1, 50),
        ], \JSON_THROW_ON_ERROR));

        $this->relate($fields, $pageLevel2, 'node_references');
        $this->entityManager->flush();
    }
}
