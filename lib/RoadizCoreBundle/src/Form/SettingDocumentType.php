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

final class SettingDocumentType extends AbstractType
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly AbstractDocumentFactory $documentFactory,
        private readonly FilesystemOperator $documentsStorage,
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function ($value) {
                if (null !== $value) {
                    $manager = $this->managerRegistry
                        ->getManagerForClass(Document::class) ?? throw new \RuntimeException('No manager found for Document class.');
                    /** @var Document|null $document */
                    $document = $manager->find(Document::class, $value);
                    if (null !== $document && null !== $document->getMountPath()) {
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
                        $manager = $this->managerRegistry
                            ->getManagerForClass(Document::class) ?? throw new \RuntimeException('No manager found for Document class.');
                        $manager->persist($document);
                        $manager->flush();

                        return $document->getId();
                    }
                }

                return null;
            }
        ));
    }

    #[\Override]
    public function getParent(): ?string
    {
        return FileType::class;
    }
}
