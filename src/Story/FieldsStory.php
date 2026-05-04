<?php

declare(strict_types=1);

namespace App\Story;

use App\GeneratedEntity\NSFields;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesToNodes;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Node\UniqueNodeGenerator;
use Zenstruck\Foundry\Story;

use function Zenstruck\Foundry\faker;

final class FieldsStory extends Story
{
    public function __construct(
        private readonly UniqueNodeGenerator $uniqueNodeGenerator,
        private readonly NodeTypes $nodeTypesBag,
        private readonly ManagerRegistry $managerRegistry,
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
        $pageLevel2 = PageHierarchyStory::get('pageLevel2');
        if (!$homePage instanceof NodesSources || !$pageLevel2 instanceof NodesSources) {
            throw new \RuntimeException('Page hierarchy story states are invalid.');
        }

        $fieldsType = $this->getNodeType('Fields');
        $manager = $this->managerRegistry->getManagerForClass(Node::class);
        if (null === $manager) {
            throw new \RuntimeException('No entity manager found for Node class.');
        }

        $fields = $this->uniqueNodeGenerator->generate(
            nodeType: $fieldsType,
            translation: $translation,
            parent: $homePage->getNode(),
            flush: false,
        );
        $fields->setTitle('Fields test node');
        $fields->setPublishedAt(new \DateTime('-1 day'));
        $fields->getNode()->setStatus(NodeStatus::PUBLISHED);

        if ($fields instanceof NSFields) {
            $fields->setSubTitle('All field types coverage');
            $fields->setLongText(faker()->paragraph(3));
            $fields->setContent(faker()->paragraphs(2, true));
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
            $fields->setYaml("k1: " . faker()->word() . "\nk2:\n  - " . faker()->word());
            $fields->setJson((string) json_encode([
                'enabled' => true,
                'max' => faker()->numberBetween(1, 50),
            ], \JSON_THROW_ON_ERROR));
        }

        $nodeReferenceRelation = (new NodesToNodes($fields->getNode(), $pageLevel2->getNode()))
            ->setFieldName('node_references')
            ->setPosition(1);
        $manager->persist($nodeReferenceRelation);
        $manager->flush();

        $this->addState('fieldsNode', $fields);
    }

    private function getNodeType(string $name): NodeType
    {
        return $this->nodeTypesBag->get($name) ?? throw new \RuntimeException(sprintf('%s node type is missing.', $name));
    }
}
