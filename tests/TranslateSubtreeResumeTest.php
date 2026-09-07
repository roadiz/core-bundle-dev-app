<?php

declare(strict_types=1);

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Node\NodeOffspringResolverInterface;
use RZ\Roadiz\CoreBundle\Node\NodeTranslator;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodeRepository;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * A subtree translation that stops halfway must stay resumable from the back office.
 */
final class TranslateSubtreeResumeTest extends KernelTestCase
{
    public function testTranslatedRootStillOffersTheLanguageForItsDescendants(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine')->getManager();
        /** @var TranslationRepository $translationRepository */
        $translationRepository = $container->get(TranslationRepository::class);
        /** @var AllStatusesNodeRepository $nodeRepository */
        $nodeRepository = $container->get(AllStatusesNodeRepository::class);
        /** @var NodeOffspringResolverInterface $offspringResolver */
        $offspringResolver = $container->get(NodeOffspringResolverInterface::class);
        /** @var NodeTranslator $nodeTranslator */
        $nodeTranslator = $container->get(NodeTranslator::class);

        // Rolled back at the end: this must not leak a locale into the other tests.
        $entityManager->beginTransaction();

        try {
            $sourceTranslation = $translationRepository->findDefault();
            self::assertInstanceOf(Translation::class, $sourceTranslation);

            $destinationTranslation = new Translation();
            $destinationTranslation->setName('resume-test');
            $destinationTranslation->setLocale('sv');
            $entityManager->persist($destinationTranslation);
            $entityManager->flush();

            $root = $nodeRepository->findOneBy(['nodeName' => 'page-level-1']);
            self::assertNotNull($root);
            $subtreeNodeIds = $offspringResolver->getAllOffspringIds($root);
            self::assertGreaterThan(1, count($subtreeNodeIds), 'This test needs a node with descendants.');

            // Half-done run: the root gets translated, its descendants do not.
            $nodeTranslator->translateNode($sourceTranslation, $destinationTranslation, $root, false);
            $entityManager->flush();

            $nodeOnly = $this->locales($translationRepository->findUnavailableTranslationsForNode($root));
            $subtree = $this->locales($translationRepository->findIncompleteTranslationsForNodes($subtreeNodeIds));

            // Node-scoped: the root has it now, so the form would offer nothing — the dead end.
            self::assertNotContains('sv', $nodeOnly);
            // Subtree-scoped: descendants still miss it, so the run stays resumable.
            self::assertContains('sv', $subtree);
        } finally {
            $entityManager->rollback();
        }
    }

    /**
     * @param TranslationInterface[] $translations
     *
     * @return string[]
     */
    private function locales(array $translations): array
    {
        return array_map(fn (TranslationInterface $translation) => $translation->getLocale(), $translations);
    }
}
