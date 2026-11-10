<?php

declare(strict_types=1);

namespace App\Story;

use App\GeneratedEntity\NSArticle;
use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\CoreBundle\Entity\TagTranslation;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Tag\TagFactory;
use Zenstruck\Foundry\Story;

final class TagStory extends Story
{
    private const string CHILDREN_COLOR = '#1f7a8c';

    public function __construct(
        private readonly TagFactory $tagFactory,
        private readonly EntityManagerInterface $manager,
    ) {
    }

    #[\Override]
    public function build(): void
    {
        $translation = AbstractNodeStory::expect(TranslationsStory::get('defaultTranslation'), Translation::class);
        $frenchTranslation = AbstractNodeStory::expect(TranslationsStory::get('frenchTranslation'), Translation::class);
        $manager = $this->manager;

        $parentTag = $this->tagFactory->create('themes', $translation);
        $parentTag->setColor(self::CHILDREN_COLOR);
        $manager->flush();

        $children = [];
        foreach (['backend', 'frontend', 'api', 'devops'] as $name) {
            $childTag = $this->tagFactory->create($name, $translation, $parentTag);
            $childTag->setColor(self::CHILDREN_COLOR);
            $children[] = $childTag;
        }
        $manager->flush();

        foreach ([
            [$parentTag, 'Themes'],
            [$children[0], 'Backend'],
            [$children[1], 'Frontend'],
            [$children[2], 'API'],
            [$children[3], 'DevOps'],
        ] as [$tag, $name]) {
            $manager->persist(
                (new TagTranslation($tag, $frenchTranslation))
                    ->setName($name)
            );
        }

        $articles = ArticleStory::getPool('articles');
        AbstractNodeStory::expect($articles[0] ?? null, NSArticle::class)->getNode()->addTag($children[0])->addTag($children[2]);
        AbstractNodeStory::expect($articles[1] ?? null, NSArticle::class)->getNode()->addTag($children[1]);
        AbstractNodeStory::expect($articles[2] ?? null, NSArticle::class)->getNode()->addTag($children[3]);

        $manager->flush();
    }
}
