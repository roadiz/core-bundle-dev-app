<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\Node;
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
            // @phpstan-ignore-next-line
            nodeType: $this->getReference(NodeTypeFixtures::NS_ARTICLE_CONTAINER),
            // @phpstan-ignore-next-line
            translation: $this->getReference(AppFixtures::DEFAULT_TRANSLATION_REFERENCE),
            flush: false,
        );
        $articleContainer->setTitle('Articles container');
        $articleContainer->getNode()->setStatus(Node::PUBLISHED);
        $articleContainer->setPublishedAt(new \DateTime());
        $manager->flush();

        for ($i = 0; $i < 50; ++$i) {
            $article = $this->uniqueNodeGenerator->generate(
                // @phpstan-ignore-next-line
                nodeType: $this->getReference(NodeTypeFixtures::NS_ARTICLE),
                // @phpstan-ignore-next-line
                translation: $this->getReference(AppFixtures::DEFAULT_TRANSLATION_REFERENCE),
                parent: $articleContainer->getNode(),
                flush: false,
            );
            $article->getNode()->setStatus(Node::PUBLISHED);
            $article->setPublishedAt(new \DateTime());
        }

        $manager->flush();
    }
}
