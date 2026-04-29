<?php

declare(strict_types=1);

namespace App\Story;

use App\GeneratedEntity\NSArticle;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesToNodes;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Node\UniqueNodeGenerator;
use Zenstruck\Foundry\Story;

use function Zenstruck\Foundry\faker;

final class ArticleStory extends Story
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

        $manager = $this->managerRegistry->getManagerForClass(Node::class);
        if (null === $manager) {
            throw new \RuntimeException('No entity manager found for Node class.');
        }

        $articleContainerType = $this->getNodeType('ArticleContainer');
        $articleType = $this->getNodeType('Article');

        $container = $this->uniqueNodeGenerator->generate(
            nodeType: $articleContainerType,
            translation: $translation,
            flush: false,
        );
        $container->setTitle('Articles container');
        $container->setPublishedAt(new \DateTime('-2 days'));
        $container->getNode()->setStatus(NodeStatus::PUBLISHED);
        $container->getNode()->setShadow(true);
        $container->getNode()->setHidingChildren(true);
        $container->getNode()->setNodeName('articles');

        $publishedArticles = [];
        for ($i = 1; $i <= 20; ++$i) {
            $article = $this->uniqueNodeGenerator->generate(
                nodeType: $articleType,
                translation: $translation,
                parent: $container->getNode(),
                flush: false,
            );

            $article->setTitle('Article '.$i);
            $article->setPublishedAt(new \DateTime('-'.$i.' hours'));
            $article->getNode()->setStatus(NodeStatus::PUBLISHED);

            if ($article instanceof NSArticle) {
                $article->setContent(faker()->paragraph(3));
                $article->setRealmASecret(faker()->sha1());
                $article->setRealmBSecret(faker()->sha1());
                if (0 === $i % 5) {
                    $article->setOnlyOnWebresponse(faker()->sentence(4));
                }
            }

            $publishedArticles[] = $article;
            $this->addToPool('articles', $article);
        }

        $draftArticle = $this->uniqueNodeGenerator->generate(
            nodeType: $articleType,
            translation: $translation,
            parent: $container->getNode(),
            flush: false,
        );
        $draftArticle->setTitle('Draft article');
        $draftArticle->setPublishedAt(new \DateTime('+1 day'));
        $draftArticle->getNode()->setStatus(NodeStatus::DRAFT);

        if ($draftArticle instanceof NSArticle) {
            $draftArticle->setContent(faker()->paragraph(2));
        }

        $relatedArticle1 = (new NodesToNodes($publishedArticles[0]->getNode(), $publishedArticles[1]->getNode()))
            ->setFieldName('related_article')
            ->setPosition(1);
        $manager->persist($relatedArticle1);

        $relatedArticle2 = (new NodesToNodes($publishedArticles[0]->getNode(), $publishedArticles[2]->getNode()))
            ->setFieldName('related_article')
            ->setPosition(2);
        $manager->persist($relatedArticle2);

        $manager->flush();

        $this->addState('articleContainer', $container);
        $this->addState('firstArticle', $publishedArticles[0]);
    }

    private function getNodeType(string $name): NodeType
    {
        return $this->nodeTypesBag->get($name) ?? throw new \RuntimeException(sprintf('%s node type is missing.', $name));
    }
}
