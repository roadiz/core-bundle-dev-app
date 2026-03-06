<?php

declare(strict_types=1);

namespace App\Story;

use App\GeneratedEntity\NSArticle;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\TagTranslation;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Tag\TagFactory;
use Zenstruck\Foundry\Story;

final class TagStory extends Story
{
    private const string CHILDREN_COLOR = '#1f7a8c';

    public function __construct(
        private readonly TagFactory $tagFactory,
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

        $manager = $this->managerRegistry->getManagerForClass(Tag::class);
        if (null === $manager) {
            throw new \RuntimeException('No entity manager found for Tag class.');
        }

        $parentTag = $this->tagFactory->create('themes', $translation);
        $parentTag->setColor(self::CHILDREN_COLOR);
        $manager->flush();

        $children = [];
        foreach (['backend', 'frontend', 'api', 'devops'] as $name) {
            $childTag = $this->tagFactory->create($name, $translation, $parentTag);
            $childTag->setColor(self::CHILDREN_COLOR);
            $children[] = $childTag;
            $this->addToPool('childTags', $childTag);
            $this->addState($name.'Tag', $childTag);
        }
        $manager->flush();

        $frenchTranslation = TranslationsStory::get('frenchTranslation');
        if ($frenchTranslation instanceof Translation) {
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
        }

        $articles = ArticleStory::getPool('articles');
        if (($articles[0] ?? null) instanceof NSArticle) {
            $articles[0]->getNode()->addTag($children[0]);
            $articles[0]->getNode()->addTag($children[2]);
        }
        if (($articles[1] ?? null) instanceof NSArticle) {
            $articles[1]->getNode()->addTag($children[1]);
        }
        if (($articles[2] ?? null) instanceof NSArticle) {
            $articles[2]->getNode()->addTag($children[3]);
        }

        $manager->flush();

        $this->addState('parentTag', $parentTag);
    }
}
