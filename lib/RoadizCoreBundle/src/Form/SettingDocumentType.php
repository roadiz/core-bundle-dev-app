<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\AbstractDocumentFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SettingDocumentType extends AbstractType
{
    protected ManagerRegistry $managerRegistry;
    protected AbstractDocumentFactory $documentFactory;
    protected FilesystemOperator $documentsStorage;

    public function __construct(
        ManagerRegistry $managerRegistry,
        AbstractDocumentFactory $documentFactory,
        FilesystemOperator $documentsStorage,
    ) {
        $this->documentFactory = $documentFactory;
        $this->managerRegistry = $managerRegistry;
        $this->documentsStorage = $documentsStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function ($value) {
                if (null !== $value) {
                    $manager = $this->managerRegistry->getManagerForClass(Document::class);
                    /** @var Document|null $document */
                    $document = $manager->find(Document::class, $value);
                    if (null !== $document) {
                        // transform the array to a string
                        return new File($this->documentsStorage->publicUrl($document->getMountPath()), false);
                    }
                }

                return null;
            },
            function ($file) {
                if ($file instanceof UploadedFile && $file->isValid()) {
                    $this->documentFactory->setFile($file);
                    $document = $this->documentFactory->getDocument();

                    if ($document instanceof Document) {
                        $manager = $this->managerRegistry->getManagerForClass(Document::class);
                        $manager->persist($document);
                        $manager->flush();

                        return $document->getId();
                    }
                }

                return null;
            }
        ));
    }

    public function getParent(): ?string
    {
        return FileType::class;
    }
}
