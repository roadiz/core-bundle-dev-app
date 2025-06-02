<?php

declare(strict_types=1);

namespace RZ\Roadiz\FontBundle\Doctrine\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\FontBundle\Entity\Font;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Handle file management on Fonts lifecycle events.
 */
#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
#[AsDoctrineListener(event: Events::preRemove)]
#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
final class FontLifeCycleSubscriber
{
    private static array $formats = ['svg', 'otf', 'eot', 'woff', 'woff2'];

    public function __construct(
        private readonly FilesystemOperator $fontStorage,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        // perhaps you only want to act on some "Font" entity
        if ($entity instanceof Font) {
            $this->setFontFilesNames($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        // perhaps you only want to act on some "Font" entity
        if ($entity instanceof Font) {
            $this->setFontFilesNames($entity);
        }
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        // perhaps you only want to act on some "Font" entity
        if ($entity instanceof Font) {
            $this->upload($entity);
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        // perhaps you only want to act on some "Font" entity
        if ($entity instanceof Font) {
            $this->upload($entity);
        }
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        // perhaps you only want to act on some "Product" entity
        if ($entity instanceof Font) {
            try {
                // factorize previous code with loop
                foreach (self::$formats as $format) {
                    $getter = 'get'.\mb_strtoupper((string) $format).'Filename';
                    $relativeUrlGetter = 'get'.\mb_strtoupper((string) $format).'RelativeUrl';
                    if (null !== $entity->$getter() && $this->fontStorage->fileExists($entity->$relativeUrlGetter())) {
                        $this->fontStorage->delete($entity->$relativeUrlGetter());
                        $this->logger->info('Font file deleted', ['file' => $entity->$relativeUrlGetter()]);
                    }
                }

                /*
                 * Removing font folder if empty.
                 */
                $fontFolder = $entity->getFolder();
                if ($this->fontStorage->directoryExists($fontFolder)) {
                    $dirListing = $this->fontStorage->listContents($fontFolder);
                    $isDirEmpty = \count($dirListing->toArray()) <= 0;
                    if ($isDirEmpty) {
                        $this->logger->info('Font folder is empty, deleting…', ['folder' => $fontFolder]);
                        $this->fontStorage->deleteDirectory($fontFolder);
                    }
                }
            } catch (FilesystemException) {
                // do nothing
            }
        }
    }

    public function setFontFilesNames(Font $font): void
    {
        if ('' == $font->getHash()) {
            $font->generateHashWithSecret('default_roadiz_secret');
        }

        foreach (self::$formats as $format) {
            /** @var UploadedFile|null $file */
            $file = $font->{'get'.ucfirst((string) $format).'File'}();
            if (null !== $file) {
                $font->{'set'.\mb_strtoupper((string) $format).'Filename'}($file->getClientOriginalName());
            }
        }
    }

    /**
     * @throws FilesystemException
     */
    public function upload(Font $font): void
    {
        foreach (self::$formats as $format) {
            /** @var UploadedFile|null $file */
            $file = $font->{'get'.ucfirst((string) $format).'File'}();
            /** @var string|null $relativeUrl */
            $relativeUrl = $font->{'get'.\mb_strtoupper((string) $format).'RelativeUrl'}();
            if (null !== $file && null !== $relativeUrl) {
                $filename = $file->getPathname();
                $fontResource = fopen($file->getPathname(), 'r');
                if (false !== $fontResource) {
                    $this->fontStorage->writeStream(
                        $relativeUrl,
                        $fontResource
                    );
                    $font->{'set'.ucfirst((string) $format).'File'}(null);
                    fclose($fontResource);
                    $this->logger->info('Font file uploaded', ['file' => $relativeUrl]);
                }
            }
        }
    }
}
