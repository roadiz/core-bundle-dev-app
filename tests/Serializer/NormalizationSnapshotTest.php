<?php

declare(strict_types=1);

namespace App\Tests\Serializer;

use App\GeneratedEntity\NSPage;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\AttributeValue;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Repository\DocumentRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes SerializationFixtures entities with the groups triggering every serializer
 * enrichment, and compares the output with committed snapshots.
 *
 * Delete a snapshot file to regenerate it, then review its diff.
 */
final class NormalizationSnapshotTest extends KernelTestCase
{
    private const array VOLATILE_KEYS = ['createdAt', 'updatedAt'];

    private string $defaultTimezone;

    /**
     * Datetimes are stored without timezone and hydrated in PHP default one:
     * force it so snapshots do not depend on the machine.
     */
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->defaultTimezone = date_default_timezone_get();
        date_default_timezone_set('UTC');
    }

    #[\Override]
    protected function tearDown(): void
    {
        date_default_timezone_set($this->defaultTimezone);
        parent::tearDown();
    }

    /**
     * @return iterable<string, array{class-string, array<string, mixed>, list<string>}>
     */
    public static function provideCases(): iterable
    {
        yield 'document_image' => [Document::class, ['filename' => 'serialization_image.jpg'], ['document_display', 'document_folders', 'document_raw_relative_path', 'explorer_thumbnail']];
        yield 'document_image_all_folders' => [Document::class, ['filename' => 'serialization_image.jpg'], ['document_display', 'document_folders_all']];
        yield 'document_video_sources' => [Document::class, ['filename' => 'serialization_video.mp4'], ['document_display', 'document_display_sources']];
        yield 'document_audio_sources' => [Document::class, ['filename' => 'serialization_sound.mp3'], ['document_display', 'document_display_sources']];
        yield 'document_pdf' => [Document::class, ['filename' => 'serialization_file.pdf'], ['document_display']];
        yield 'document_embed' => [Document::class, ['filename' => 'serialization_embed.jpg'], ['document_display']];
        yield 'folder' => [Folder::class, ['folderName' => 'serialization-visible'], ['folder', 'document_display']];
        yield 'tag' => [Tag::class, ['tagName' => 'serialization-tag'], ['tag', 'tag_base', 'tag_documents', 'tag_color']];
        yield 'custom_form' => [CustomForm::class, ['name' => 'serialization-form'], ['custom_form', 'urls']];
        yield 'nodes_sources' => [NSPage::class, ['title' => 'Serialization page'], ['nodes_sources', 'nodes_sources_base', 'urls', 'tag_base', 'node_attributes', 'attribute', 'attribute_documents', 'document_display']];
    }

    /**
     * @param class-string         $class
     * @param array<string, mixed> $criteria
     * @param list<string>         $groups
     *
     * @dataProvider provideCases
     */
    public function testNormalizationMatchesSnapshot(string $class, array $criteria, array $groups): void
    {
        /** @var ManagerRegistry $managerRegistry */
        $managerRegistry = static::getContainer()->get(ManagerRegistry::class);
        $entity = $managerRegistry->getRepository($class)->findOneBy($criteria);
        $this->assertNotNull($entity, sprintf('Missing %s fixture, run doctrine:fixtures:load.', $class));
        $translation = $managerRegistry->getRepository(Translation::class)->findOneBy(['defaultTranslation' => true]);

        /** @var NormalizerInterface $serializer */
        $serializer = static::getContainer()->get('serializer');
        $context = ['groups' => $groups, 'translation' => $translation];

        $this->assertSnapshot(
            (string) $this->dataName(),
            [
                'jsonld' => $serializer->normalize($entity, 'jsonld', $context),
                'json' => $serializer->normalize($entity, 'json', $context),
            ]
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideDocumentDtoCases(): iterable
    {
        yield 'document_dto_video_sources' => ['serialization_video.mp4'];
        yield 'document_dto_audio_sources' => ['serialization_sound.mp3'];
    }

    /**
     * @dataProvider provideDocumentDtoCases
     */
    public function testDocumentDtoNormalizationMatchesSnapshot(string $filename): void
    {
        /** @var ManagerRegistry $managerRegistry */
        $managerRegistry = static::getContainer()->get(ManagerRegistry::class);
        /** @var DocumentRepository $repository */
        $repository = $managerRegistry->getRepository(Document::class);
        $dtos = $repository->findDtoByFilenames([$filename]);
        $this->assertCount(1, $dtos, 'Missing Document fixture, run doctrine:fixtures:load.');

        /** @var NormalizerInterface $serializer */
        $serializer = static::getContainer()->get('serializer');
        $context = ['groups' => ['document_display', 'document_display_sources']];

        $this->assertSnapshot(
            (string) $this->dataName(),
            [
                'jsonld' => $serializer->normalize($dtos[0], 'jsonld', $context),
                'json' => $serializer->normalize($dtos[0], 'json', $context),
            ]
        );
    }

    public function testAttributeValuesMatchSnapshot(): void
    {
        /** @var ManagerRegistry $managerRegistry */
        $managerRegistry = static::getContainer()->get(ManagerRegistry::class);
        $translation = $managerRegistry->getRepository(Translation::class)->findOneBy(['defaultTranslation' => true]);
        $attributeValues = $managerRegistry->getRepository(AttributeValue::class)->findBy([], ['id' => 'ASC']);
        $this->assertNotEmpty($attributeValues);

        /** @var NormalizerInterface $serializer */
        $serializer = static::getContainer()->get('serializer');
        $context = ['groups' => ['urls', 'document_display', 'attribute', 'attribute_documents', 'position'], 'translation' => $translation];

        $normalized = [];
        foreach ($attributeValues as $attributeValue) {
            $normalized[$attributeValue->getAttribute()?->getCode() ?? ''] = [
                'jsonld' => $serializer->normalize($attributeValue, 'jsonld', $context),
                'json' => $serializer->normalize($attributeValue, 'json', $context),
            ];
        }
        $this->assertSnapshot('attribute_values', $normalized);
    }

    private function assertSnapshot(string $name, array $normalized): void
    {
        $actual = json_encode($this->scrub($normalized), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR)."\n";
        $file = __DIR__.'/snapshots/'.$name.'.json';

        if (!file_exists($file)) {
            file_put_contents($file, $actual);
            $this->markTestIncomplete(sprintf('Snapshot %s created, review it then run the test again.', $file));
        }

        $this->assertSame(file_get_contents($file), $actual);
    }

    /**
     * Removes timestamps, and replaces auto-increment ids and random JSON-LD genids,
     * which depend on database history.
     */
    private function scrub(mixed $data): mixed
    {
        if (\is_string($data)) {
            return preg_replace(
                ['#/\.well-known/genid/[0-9a-f]+#', '#(/api/[a-z_]+/)\d+#'],
                ['/.well-known/genid/{genid}', '$1{id}'],
                $data
            );
        }
        if (!\is_array($data)) {
            return $data;
        }

        $scrubbed = [];
        foreach (array_diff_key($data, array_flip(self::VOLATILE_KEYS)) as $key => $value) {
            $scrubbed[$key] = 'id' === $key && \is_int($value) ? '{id}' : $this->scrub($value);
        }

        return $scrubbed;
    }
}
