<?php

declare(strict_types=1);

namespace App\Tests;

use App\GeneratedEntity\NSPage;
use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Node\UniversalDataDuplicator;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Universal documents replace the destination ones, even when the destination source
 * documentsByFields collection is already loaded: persisting the new NodesSourcesDocuments
 * cascades to that collection, and Doctrine would otherwise manage the removed ones again.
 */
final class UniversalDataDuplicatorTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->entityManager = $entityManager;
        // Rolled back in tearDown: nodes, documents and the extra locale must not leak into other tests.
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        parent::tearDown();
    }

    public function testReplacesDocumentsOfLoadedDestinationSource(): void
    {
        /** @var NodeTypes $nodeTypes */
        $nodeTypes = static::getContainer()->get(NodeTypes::class);
        $field = $nodeTypes->get('Page')?->getFieldByName('images');
        self::assertInstanceOf(NodeTypeField::class, $field);
        self::assertTrue($field->isUniversal());

        /** @var TranslationRepository $translationRepository */
        $translationRepository = static::getContainer()->get(TranslationRepository::class);
        $defaultTranslation = $translationRepository->findDefault();
        self::assertInstanceOf(Translation::class, $defaultTranslation);
        $secondaryTranslation = new Translation();
        $secondaryTranslation->setName('universal-test');
        $secondaryTranslation->setLocale('sv');
        $this->entityManager->persist($secondaryTranslation);

        $node = new Node();
        $node->setNodeName('universal-documents-test');
        $node->setNodeTypeName('Page');
        $this->entityManager->persist($node);
        $defaultSource = new NSPage($node, $defaultTranslation);
        $defaultSource->setTitle('Universal documents test');
        $secondarySource = new NSPage($node, $secondaryTranslation);
        $secondarySource->setTitle('Universal documents test');
        $this->entityManager->persist($defaultSource);
        $this->entityManager->persist($secondarySource);

        $universalDocument = $this->createDocument('universal_test_a.jpg');
        $formerDocument = $this->createDocument('universal_test_b.jpg');
        $this->entityManager->persist(new NodesSourcesDocuments($defaultSource, $universalDocument, $field));
        $this->entityManager->persist(new NodesSourcesDocuments($secondarySource, $formerDocument, $field));
        $this->entityManager->flush();
        $this->entityManager->clear();

        $defaultSource = $this->entityManager->find(NodesSources::class, $defaultSource->getId());
        $secondarySource = $this->entityManager->find(NodesSources::class, $secondarySource->getId());
        self::assertInstanceOf(NodesSources::class, $defaultSource);
        self::assertInstanceOf(NodesSources::class, $secondarySource);
        // Any reader of the destination source (form, explorer, serializer) loads this collection.
        self::assertCount(1, $secondarySource->getDocumentsByFields());

        /** @var UniversalDataDuplicator $duplicator */
        $duplicator = static::getContainer()->get(UniversalDataDuplicator::class);
        self::assertTrue($duplicator->duplicateUniversalContents($defaultSource));
        $this->entityManager->flush();
        $this->entityManager->clear();

        $documents = $this->entityManager->getRepository(NodesSourcesDocuments::class)->findBy([
            'nodeSource' => $secondarySource->getId(),
            'fieldName' => $field->getName(),
        ]);
        self::assertSame(
            ['universal_test_a.jpg'],
            array_map(fn (NodesSourcesDocuments $nsDoc) => $nsDoc->getDocument()->getFilename(), $documents),
        );
    }

    private function createDocument(string $filename): Document
    {
        $document = new Document();
        $document->setFilename($filename);
        $document->setFolder('c9d8e7f6');
        $document->setMimeType('image/jpeg');
        $this->entityManager->persist($document);

        return $document;
    }
}
