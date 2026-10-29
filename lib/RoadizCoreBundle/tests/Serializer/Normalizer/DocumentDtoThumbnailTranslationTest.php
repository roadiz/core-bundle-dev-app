<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Serializer\Normalizer;

use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Model\DocumentDto;
use RZ\Roadiz\CoreBundle\Repository\DocumentRepository;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A non-image DocumentDto exposes its thumbnail with the translated fields (alt, copyright…) of the
 * serialization context translation, falling back to the default translation, like the Document entity.
 */
final class DocumentDtoThumbnailTranslationTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->entityManager = $entityManager;
        // Rolled back in tearDown: documents and the extra locale must not leak into other tests.
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        parent::tearDown();
    }

    public function testThumbnailUsesContextTranslationFields(): void
    {
        $secondary = $this->createSecondaryTranslation();
        $video = $this->createVideoWithThumbnail([
            $this->defaultTranslation()->getLocale() => ['Poster', '© Default', 'Default description'],
            $secondary->getLocale() => ['Affiche', '© Secondaire', 'Description secondaire'],
        ]);

        $thumbnail = $this->normalizeThumbnail($video, $secondary);

        self::assertSame('Affiche', $thumbnail['name'] ?? null);
        self::assertSame('Affiche', $thumbnail['alt'] ?? null);
        self::assertSame('© Secondaire', $thumbnail['copyright'] ?? null);
        self::assertSame('Description secondaire', $thumbnail['description'] ?? null);
    }

    public function testThumbnailDoesNotMixTranslationFields(): void
    {
        $secondary = $this->createSecondaryTranslation();
        $video = $this->createVideoWithThumbnail([
            $this->defaultTranslation()->getLocale() => ['Poster', '© Default', 'Default description'],
            $secondary->getLocale() => ['Affiche', null, null],
        ]);

        $thumbnail = $this->normalizeThumbnail($video, $secondary);

        // Like DocumentEnricher, an existing context translation row is used as a whole.
        self::assertSame('Affiche', $thumbnail['name'] ?? null);
        self::assertNull($thumbnail['copyright'] ?? null);
        self::assertNull($thumbnail['description'] ?? null);
    }

    public function testThumbnailFallsBackToDefaultTranslationFields(): void
    {
        $secondary = $this->createSecondaryTranslation();
        $video = $this->createVideoWithThumbnail([
            $this->defaultTranslation()->getLocale() => ['Poster', '© Default', 'Default description'],
        ]);

        $thumbnail = $this->normalizeThumbnail($video, $secondary);

        self::assertSame('Poster', $thumbnail['name'] ?? null);
        self::assertSame('Poster', $thumbnail['alt'] ?? null);
        self::assertSame('© Default', $thumbnail['copyright'] ?? null);
        self::assertSame('Default description', $thumbnail['description'] ?? null);
    }

    private function defaultTranslation(): Translation
    {
        /** @var TranslationRepository $repository */
        $repository = static::getContainer()->get(TranslationRepository::class);
        $translation = $repository->findDefault();
        self::assertInstanceOf(Translation::class, $translation);

        return $translation;
    }

    private function createSecondaryTranslation(): Translation
    {
        $translation = new Translation();
        $translation->setName('thumbnail-test');
        $translation->setLocale('sv');
        $this->entityManager->persist($translation);
        $this->entityManager->flush();

        return $translation;
    }

    /**
     * @param array<string, array{0: string, 1: ?string, 2: ?string}> $thumbnailTranslations name, copyright and description per locale
     */
    private function createVideoWithThumbnail(array $thumbnailTranslations): Document
    {
        $video = new Document();
        $video->setFilename('thumbnail-test-video.mp4');
        $video->setFolder('a1b2c3d4');
        $video->setMimeType('video/mp4');
        $this->entityManager->persist($video);

        $thumbnail = new Document();
        $thumbnail->setFilename('thumbnail-test-poster.jpg');
        $thumbnail->setFolder('e5f6a7b8');
        $thumbnail->setMimeType('image/jpeg');
        $thumbnail->setOriginal($video);
        $this->entityManager->persist($thumbnail);

        /** @var TranslationRepository $translationRepository */
        $translationRepository = static::getContainer()->get(TranslationRepository::class);
        foreach ($thumbnailTranslations as $locale => [$name, $copyright, $description]) {
            $translation = $translationRepository->findOneByLocale($locale);
            self::assertInstanceOf(Translation::class, $translation);
            $documentTranslation = new DocumentTranslation();
            $documentTranslation->setDocument($thumbnail)->setTranslation($translation)->setName($name)->setCopyright($copyright)->setDescription($description);
            $this->entityManager->persist($documentTranslation);
        }
        $this->entityManager->flush();

        return $video;
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeThumbnail(Document $video, Translation $translation): array
    {
        /** @var DocumentRepository $repository */
        $repository = static::getContainer()->get(DocumentRepository::class);
        $dtos = $repository->findDtoByFilenames([(string) $video->getFilename()]);
        self::assertCount(1, $dtos);
        self::assertInstanceOf(DocumentDto::class, $dtos[0]);

        /** @var NormalizerInterface $serializer */
        $serializer = static::getContainer()->get('serializer');
        $normalized = $serializer->normalize($dtos[0], 'json', [
            'groups' => ['document_display', 'document_thumbnails'],
            'translation' => $translation,
        ]);
        self::assertIsArray($normalized);
        self::assertIsArray($normalized['thumbnail'] ?? null, 'Non-image documents must expose their thumbnail.');

        return $normalized['thumbnail'];
    }
}
