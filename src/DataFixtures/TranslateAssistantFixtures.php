<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\GeneratedEntity\NSArticle;
use App\GeneratedEntity\NSMenuLink;
use App\GeneratedEntity\NSPage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Node\UniqueNodeGenerator;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;

/**
 * A small English subtree to exercise the machine-translation of a node tree.
 *
 * Not part of the default fixture set — load it on demand:
 *     bin/console doctrine:fixtures:load --group=translate-assistant --append
 */
final class TranslateAssistantFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private readonly UniqueNodeGenerator $uniqueNodeGenerator,
        private readonly NodeTypes $nodeTypesBag,
        private readonly TranslationRepository $translationRepository,
    ) {
    }

    #[\Override]
    public static function getGroups(): array
    {
        return ['translate-assistant'];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $translation = $this->translationRepository->findDefault();
        if (null === $translation) {
            throw new \RuntimeException('No default translation, run app:install first.');
        }

        $root = $this->page($translation, null, 'Translate root', 'The subtree to translate', 'Machine translation');
        $root->setContent(<<<'MARKDOWN'
            ## What this page is for

            This page and its children are here to check what the translate assistant
            actually touches. **Bold** and *italic* should survive the round-trip.

            - a first item
            - a second item
            MARKDOWN);
        $root->setMetaTitle('Translate root — SEO title');
        $root->setMetaDescription('A short SEO description that should come back translated.');
        // Universal + excluded from search, but NOT from translation: stays as-is because colour is not a prose type.
        $root->setColor('#3f51b5');

        $child = $this->page($translation, $root->getNode(), 'Child page', 'A nested page', 'Second level');
        $child->setContent('A child page, translated by its own message in the queue.');

        $grandChild = $this->page($translation, $child->getNode(), 'Grand-child page', 'Third level', 'Deep');
        $grandChild->setContent('Third level: only translated when *Translate node hierarchy* is checked.');

        $article = $this->create(NSArticle::class, 'Article', $translation, $root->getNode(), 'Translate article');
        $article->setContent('An article with **markdown** content and three secrets that must not be translated.');
        // excludeFromTranslation: true in config/node_types/article.yaml — must stay identical.
        $article->setRealmASecret('KEEP-ME-VERBATIM-A');
        $article->setRealmBSecret('KEEP-ME-VERBATIM-B');
        $article->setOnlyOnWebresponse('KEEP-ME-VERBATIM-WEBRESPONSE');

        $menuLink = $this->create(NSMenuLink::class, 'MenuLink', $translation, $root->getNode(), 'Translate menu link');
        // excludeFromTranslation: true in config/node_types/menulink.yaml — must stay identical.
        $menuLink->setLinkExternalUrl('https://www.roadiz.io/documentation');

        $manager->flush();
    }

    private function page(
        TranslationInterface $translation,
        ?Node $parent,
        string $title,
        string $subTitle,
        string $overTitle,
    ): NSPage {
        $page = $this->create(NSPage::class, 'Page', $translation, $parent, $title);
        $page->setSubTitle($subTitle);
        $page->setOverTitle($overTitle);

        return $page;
    }

    /**
     * @template T of NodesSources
     *
     * @param class-string<T> $sourceClass
     *
     * @return T
     */
    private function create(
        string $sourceClass,
        string $nodeTypeName,
        TranslationInterface $translation,
        ?Node $parent,
        string $title,
    ): NodesSources {
        $nodeType = $this->nodeTypesBag->get($nodeTypeName);
        if (null === $nodeType) {
            throw new \RuntimeException(sprintf('Node-type "%s" does not exist.', $nodeTypeName));
        }

        $source = $this->uniqueNodeGenerator->generate($nodeType, $translation, $parent, flush: false);
        if (!$source instanceof $sourceClass) {
            throw new \RuntimeException(sprintf('Expected a %s, got %s.', $sourceClass, $source::class));
        }

        $source->setTitle($title);
        // Node name is left to UniqueNodeGenerator: nodes.node_name is unique, so a fixed name
        // would make this fixture loadable only once.
        $source->getNode()->setStatus(NodeStatus::PUBLISHED);
        $source->setPublishedAt(new \DateTime());

        return $source;
    }
}
