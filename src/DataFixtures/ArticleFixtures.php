<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Node\UniqueNodeGenerator;

class ArticleFixtures extends Fixture implements DependentFixtureInterface
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
        $articleContainer = $this->uniqueNodeGenerator->generate(
            nodeType: $this->getReference(NodeTypeFixtures::NS_ARTICLE_CONTAINER, NodeType::class),
            translation: $this->getReference(AppFixtures::DEFAULT_TRANSLATION_REFERENCE, Translation::class),
            flush: false,
        );
        $articleContainer->setTitle('Articles container');
        $articleContainer->getNode()->setStatus(NodeStatus::PUBLISHED);
        $articleContainer->setPublishedAt(new \DateTime());
        $manager->flush();

        for ($i = 0; $i < 50; ++$i) {
            $article = $this->uniqueNodeGenerator->generate(
                nodeType: $this->getReference(NodeTypeFixtures::NS_ARTICLE, NodeType::class),
                translation: $this->getReference(AppFixtures::DEFAULT_TRANSLATION_REFERENCE, Translation::class),
                parent: $articleContainer->getNode(),
                flush: false,
            );
            $article->getNode()->setStatus(NodeStatus::PUBLISHED);
            $article->setPublishedAt(new \DateTime());
        }

        $manager->flush();
    }
}
