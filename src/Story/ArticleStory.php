<?php

declare(strict_types=1);

namespace App\Story;

use App\GeneratedEntity\NSArticle;
use App\GeneratedEntity\NSArticleContainer;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;

use function Zenstruck\Foundry\faker;

final class ArticleStory extends AbstractNodeStory
{
    #[\Override]
    public function build(): void
    {
        $container = $this->publishedNode('ArticleContainer', NSArticleContainer::class, 'Articles container', null, '-2 days');
        $container->getNode()->setShadow(true);
        $container->getNode()->setHidingChildren(true);
        $container->getNode()->setNodeName('articles');

        $articles = [];
        for ($i = 1; $i <= 20; ++$i) {
            $article = $this->publishedNode('Article', NSArticle::class, 'Article '.$i, $container->getNode(), '-'.$i.' hours');
            $article->setContent(faker()->paragraph(3));
            $article->setRealmASecret(faker()->sha1());
            $article->setRealmBSecret(faker()->sha1());
            if (0 === $i % 5) {
                $article->setOnlyOnWebresponse(faker()->sentence(4));
            }

            $articles[] = $article;
            $this->addToPool('articles', $article);
        }

        $draft = $this->publishedNode('Article', NSArticle::class, 'Draft article', $container->getNode(), '+1 day', NodeStatus::DRAFT);
        $draft->setContent(faker()->paragraph(2));

        $this->relate($articles[0], $articles[1], 'related_article', 1);
        $this->relate($articles[0], $articles[2], 'related_article', 2);

        $this->entityManager->flush();
    }
}
