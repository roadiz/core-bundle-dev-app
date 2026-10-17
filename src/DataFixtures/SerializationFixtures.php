<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Attribute;
use RZ\Roadiz\CoreBundle\Entity\AttributeDocuments;
use RZ\Roadiz\CoreBundle\Entity\AttributeTranslation;
use RZ\Roadiz\CoreBundle\Entity\AttributeValue;
use RZ\Roadiz\CoreBundle\Entity\AttributeValueTranslation;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\FolderTranslation;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\TagTranslation;
use RZ\Roadiz\CoreBundle\Entity\TagTranslationDocuments;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Model\AttributeInterface;
use RZ\Roadiz\CoreBundle\Node\UniqueNodeGenerator;

/**
 * Entities covering every serializer enrichment, used by NormalizationSnapshotTest.
 */
class SerializationFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UniqueNodeGenerator $uniqueNodeGenerator,
        private readonly NodeTypes $nodeTypesBag,
    ) {
    }

    #[\Override]
    public function getDependencies(): array
    {
        return [
            AppFixtures::class,
        ];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $translation = $this->getReference(AppFixtures::DEFAULT_TRANSLATION_REFERENCE, Translation::class);

        $visibleFolder = (new Folder())->setFolderName('serialization-visible')->setVisible(true);
        $hiddenFolder = (new Folder())->setFolderName('serialization-hidden')->setVisible(false);
        $manager->persist($visibleFolder);
        $manager->persist($hiddenFolder);
        $manager->persist((new FolderTranslation($visibleFolder, $translation))->setName('Visible folder'));

        $raw = $this->createDocument($manager, 'serialization_image_raw.jpg', 'image/jpeg')->setRaw(true);
        $image = $this->createDocument($manager, 'serialization_image.jpg', 'image/jpeg')
            ->setImageCropAlignment('top-left')
            ->setRawDocument($raw);
        $visibleFolder->addDocument($image);
        $hiddenFolder->addDocument($image);
        $manager->persist(
            (new DocumentTranslation())
                ->setDocument($image)
                ->setTranslation($translation)
                ->setName('Image name')
                ->setDescription('Image description')
                ->setCopyright('Image copyright')
                ->setExternalUrl('https://example.test/image')
        );

        $this->createDocument($manager, 'serialization_video.mp4', 'video/mp4');
        $this->createDocument($manager, 'serialization_video.webm', 'video/webm');
        $this->createDocument($manager, 'serialization_sound.mp3', 'audio/mpeg');
        $this->createDocument($manager, 'serialization_sound.ogg', 'audio/ogg');
        $this->createDocument($manager, 'serialization_file.pdf', 'application/pdf');
        $this->createDocument($manager, 'serialization_embed.jpg', 'image/jpeg')
            ->setEmbedPlatform('youtube')
            ->setEmbedId('dQw4w9WgXcQ');

        $tag = (new Tag())->setTagName('serialization-tag')->setVisible(true)->setColor('#00ff00');
        $manager->persist($tag);
        $tagTranslation = (new TagTranslation($tag, $translation))
            ->setName('Tag name')
            ->setDescription('Tag description');
        $manager->persist($tagTranslation);
        $manager->persist(new TagTranslationDocuments($tagTranslation, $image));

        $manager->persist(
            (new CustomForm())
                ->setName('serialization-form')
                ->setDisplayName('Serialization form')
                ->setColor('#ff0000')
                ->setDescription('Form description')
                ->setOpen(true)
        );

        $page = $this->uniqueNodeGenerator->generate(
            // @phpstan-ignore-next-line
            nodeType: $this->nodeTypesBag->get('Page'),
            translation: $translation,
            flush: false,
        );
        $page->setTitle('Serialization page');
        $page->setPublishedAt(new \DateTime('2020-01-01 00:00:00'));
        $page->getNode()->setNodeName('serialization-page');
        $page->getNode()->setStatus(NodeStatus::PUBLISHED);
        $page->getNode()->addTag($tag);

        $stringAttribute = $this->createAttribute($manager, $translation, 'serialization_string', AttributeInterface::STRING_T, 'String label');
        $manager->persist(new AttributeDocuments($stringAttribute, $image));
        $this->createAttributeValue($manager, $translation, $stringAttribute, $page->getNode(), 'String value');

        $dateAttribute = $this->createAttribute($manager, $translation, 'serialization_date', AttributeInterface::DATETIME_T, 'Date label');
        $this->createAttributeValue($manager, $translation, $dateAttribute, $page->getNode(), new \DateTime('2021-02-03 04:05:06'));

        $manager->flush();
    }

    private function createDocument(ObjectManager $manager, string $filename, string $mimeType): Document
    {
        $document = (new Document())
            ->setFilename($filename)
            ->setMimeType($mimeType)
            ->setFolder('fixtures');
        $manager->persist($document);

        return $document;
    }

    private function createAttribute(ObjectManager $manager, Translation $translation, string $code, int $type, string $label): Attribute
    {
        $attribute = (new Attribute())->setCode($code)->setType($type)->setColor('#0000ff');
        $attribute->setWeight(10);
        $manager->persist($attribute);
        $manager->persist((new AttributeTranslation())->setAttribute($attribute)->setTranslation($translation)->setLabel($label));

        return $attribute;
    }

    private function createAttributeValue(ObjectManager $manager, Translation $translation, Attribute $attribute, Node $node, mixed $value): void
    {
        $attributeValue = (new AttributeValue())->setAttribute($attribute)->setNode($node);
        $manager->persist($attributeValue);
        $manager->persist(
            (new AttributeValueTranslation())
                ->setAttributeValue($attributeValue)
                ->setTranslation($translation)
                ->setValue($value)
        );
    }
}
