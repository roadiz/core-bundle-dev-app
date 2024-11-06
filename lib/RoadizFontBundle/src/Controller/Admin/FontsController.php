<?php

declare(strict_types=1);

namespace RZ\Roadiz\FontBundle\Controller\Admin;

use JMS\Serializer\SerializerInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\FontBundle\Entity\Font;
use RZ\Roadiz\FontBundle\Event\Font\PreUpdatedFontEvent;
use RZ\Roadiz\FontBundle\Form\FontType;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Themes\Rozier\Controllers\AbstractAdminController;

class FontsController extends AbstractAdminController
{
    private FilesystemOperator $fontStorage;

    public function __construct(
        FilesystemOperator $fontStorage,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct($serializer, $urlGenerator);
        $this->fontStorage = $fontStorage;
    }

    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof Font;
    }

    protected function getNamespace(): string
    {
        return 'font';
    }

    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new Font();
    }

    protected function getTemplateFolder(): string
    {
        return '@RoadizFont/admin';
    }

    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_FONTS';
    }

    protected function getEntityClass(): string
    {
        return Font::class;
    }

    protected function getFormType(): string
    {
        return FontType::class;
    }

    protected function getDefaultOrder(Request $request): array
    {
        return ['name' => 'ASC'];
    }

    protected function getDefaultRouteName(): string
    {
        return 'fontsHomePage';
    }

    protected function getEditRouteName(): string
    {
        return 'fontsEditPage';
    }

    protected function createUpdateEvent(PersistableInterface $item): ?Event
    {
        if ($item instanceof Font) {
            return new PreUpdatedFontEvent($item);
        }

        return null;
    }

    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof Font) {
            return $item->getName();
        }
        throw new \InvalidArgumentException('Item should be instance of '.$this->getEntityClass());
    }

    /**
     * Return a ZipArchive of requested font.
     *
     * @throws FilesystemException
     */
    public function downloadAction(Request $request, int $id): BinaryFileResponse
    {
        $this->denyAccessUnlessGranted($this->getRequiredRole());

        /** @var Font|null $font */
        $font = $this->em()->find(Font::class, $id);

        if (null !== $font) {
            // Prepare File
            $file = tempnam(sys_get_temp_dir(), 'font_'.$font->getId());
            if (false === $file) {
                throw new \RuntimeException('Cannot create temporary file.');
            }
            $zip = new \ZipArchive();
            $zip->open($file, \ZipArchive::CREATE);

            if ('' != $font->getEOTFilename()) {
                $zip->addFromString($font->getEOTFilename(), $this->fontStorage->read($font->getEOTRelativeUrl()));
            }
            if ('' != $font->getSVGFilename()) {
                $zip->addFromString($font->getSVGFilename(), $this->fontStorage->read($font->getSVGRelativeUrl()));
            }
            if ('' != $font->getWOFFFilename()) {
                $zip->addFromString($font->getWOFFFilename(), $this->fontStorage->read($font->getWOFFRelativeUrl()));
            }
            if ('' != $font->getWOFF2Filename()) {
                $zip->addFromString($font->getWOFF2Filename(), $this->fontStorage->read($font->getWOFF2RelativeUrl()));
            }
            if ('' != $font->getOTFFilename()) {
                $zip->addFromString($font->getOTFFilename(), $this->fontStorage->read($font->getOTFRelativeUrl()));
            }
            // Close and send to users
            $zip->close();
            $filename = StringHandler::slugify($font->getName().' '.$font->getReadableVariant()).'.zip';

            return (new BinaryFileResponse($file, Response::HTTP_OK, [
                'content-type' => 'application/zip',
                'content-disposition' => 'attachment; filename='.$filename,
            ], false))->deleteFileAfterSend(true);
        }

        throw new ResourceNotFoundException();
    }
}
