<?php

declare(strict_types=1);

namespace RZ\Roadiz\FontBundle\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\FontBundle\Entity\Font;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Handle file management on Fonts lifecycle events.
 */
final class FontLifeCycleSubscriber implements EventSubscriber
{
    private static array $formats = ['svg', 'otf', 'eot', 'woff', 'woff2'];
    private LoggerInterface $logger;
    private FilesystemOperator $fontStorage;

    public function __construct(FilesystemOperator $fontStorage, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->fontStorage = $fontStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::preRemove,
            Events::postPersist,
            Events::postUpdate,
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        // perhaps you only want to act on some "Font" entity
        if ($entity instanceof Font) {
            $this->setFontFilesNames($entity);
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        // perhaps you only want to act on some "Font" entity
        if ($entity instanceof Font) {
            $this->setFontFilesNames($entity);
        }
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws FilesystemException
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        // perhaps you only want to act on some "Font" entity
        if ($entity instanceof Font) {
            $this->upload($entity);
        }
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws FilesystemException
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        // perhaps you only want to act on some "Font" entity
        if ($entity instanceof Font) {
            $this->upload($entity);
        }
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        // perhaps you only want to act on some "Product" entity
        if ($entity instanceof Font) {
            try {
                // factorize previous code with loop
                foreach (self::$formats as $format) {
                    $getter = 'get' . \mb_strtoupper($format) . 'Filename';
                    $relativeUrlGetter = 'get' . \mb_strtoupper($format) . 'RelativeUrl';
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
            } catch (FilesystemException $e) {
                //do nothing
            }
        }
    }

    public function setFontFilesNames(Font $font): void
    {
        if ($font->getHash() == "") {
            $font->generateHashWithSecret('default_roadiz_secret');
        }

        foreach (self::$formats as $format) {
            /** @var UploadedFile|null $file */
            $file = $font->{'get' . ucfirst($format) . 'File'}();
            if (null !== $file) {
                $font->{'set' . \mb_strtoupper($format) . 'Filename'}($file->getClientOriginalName());
            }
        }
    }

    /**
     * @param Font $font
     * @return void
     * @throws FilesystemException
     */
    public function upload(Font $font): void
    {
        foreach (self::$formats as $format) {
            /** @var UploadedFile|null $file */
            $file = $font->{'get' . ucfirst($format) . 'File'}();
            /** @var string|null $relativeUrl */
            $relativeUrl =  $font->{'get' . \mb_strtoupper($format) . 'RelativeUrl'}();
            if (null !== $file && null !== $relativeUrl) {
                $filename = $file->getPathname();
                $fontResource = fopen($file->getPathname(), 'r');
                if (false !== $fontResource) {
                    $this->fontStorage->writeStream(
                        $relativeUrl,
                        $fontResource
                    );
                    $font->{'set' . ucfirst($format) . 'File'}(null);
                    fclose($fontResource);
                    $this->logger->info('Font file uploaded', ['file' => $relativeUrl]);
                }
            }
        }
    }
}
