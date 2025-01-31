<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Importer\NodeTypesImporter;

/**
 * @deprecated nodeTypes will be static in future Roadiz versions
 */
class NodeTypeFixtures extends Fixture
{
    public const NS_ARTICLE_CONTAINER = 'ns-article-container';
    public const NS_ARTICLE = 'ns-article';
    public const NS_OFFER = 'ns-offer';
    public const NS_NEUTRAL = 'ns-neutral';

    public function __construct(
        private readonly NodeTypesImporter $nodeTypesImporter,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->nodeTypesImporter->import(
            file_get_contents(__DIR__.'/../Resources/node-types/ArticleContainer.json') ?:
                throw new \RuntimeException('Node type JSON file does not exist.')
        );
        $this->nodeTypesImporter->import(
            file_get_contents(__DIR__.'/../Resources/node-types/Article.json') ?:
                throw new \RuntimeException('Node type JSON file does not exist.')
        );
        $this->nodeTypesImporter->import(
            file_get_contents(__DIR__.'/../Resources/node-types/Offer.json') ?:
                throw new \RuntimeException('Node type JSON file does not exist.')
        );
        $this->nodeTypesImporter->import(
            file_get_contents(__DIR__.'/../Resources/node-types/Neutral.json') ?:
                throw new \RuntimeException('Node type JSON file does not exist.')
        );

        $articleContainerNodeType = $manager->getRepository(NodeType::class)->findOneBy([
            'name' => 'ArticleContainer',
        ]);
        $articleNodeType = $manager->getRepository(NodeType::class)->findOneBy([
            'name' => 'Article',
        ]);
        $offerNodeType = $manager->getRepository(NodeType::class)->findOneBy([
            'name' => 'Offer',
        ]);
        $neutralNodeType = $manager->getRepository(NodeType::class)->findOneBy([
            'name' => 'Neutral',
        ]);

        $this->addReference(self::NS_ARTICLE_CONTAINER, $articleContainerNodeType);
        $this->addReference(self::NS_ARTICLE, $articleNodeType);
        $this->addReference(self::NS_OFFER, $offerNodeType);
        $this->addReference(self::NS_NEUTRAL, $neutralNodeType);
    }
}
