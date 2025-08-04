<?php

declare(strict_types=1);

namespace RZ\Roadiz\FontBundle\Controller\Admin;

use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\FontBundle\Entity\Font;
use RZ\Roadiz\FontBundle\Event\Font\PreUpdatedFontEvent;
use RZ\Roadiz\FontBundle\Form\FontType;
use RZ\Roadiz\RozierBundle\Controller\AbstractAdminController;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FontsController extends AbstractAdminController
{
    public function __construct(
        private readonly FilesystemOperator $fontStorage,
        UrlGeneratorInterface $urlGenerator,
        EntityListManagerFactoryInterface $entityListManagerFactory,
        ManagerRegistry $managerRegistry,
        TranslatorInterface $translator,
        LogTrail $logTrail,
        EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($urlGenerator, $entityListManagerFactory, $managerRegistry, $translator, $logTrail, $eventDispatcher);
    }

    #[\Override]
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof Font;
    }

    #[\Override]
    protected function getNamespace(): string
    {
        return 'font';
    }

    #[\Override]
    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new Font();
    }

    #[\Override]
    protected function getTemplateFolder(): string
    {
        return '@RoadizFont/admin';
    }

    #[\Override]
    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_FONTS';
    }

    #[\Override]
    protected function getEntityClass(): string
    {
        return Font::class;
    }

    #[\Override]
    protected function getFormType(): string
    {
        return FontType::class;
    }

    #[\Override]
    protected function getDefaultOrder(Request $request): array
    {
        return ['name' => 'ASC'];
    }

    #[\Override]
    protected function getDefaultRouteName(): string
    {
        return 'fontsHomePage';
    }

    #[\Override]
    protected function getEditRouteName(): string
    {
        return 'fontsEditPage';
    }

    #[\Override]
    protected function createUpdateEvent(PersistableInterface $item): ?Event
    {
        if ($item instanceof Font) {
            return new PreUpdatedFontEvent($item);
        }

        return null;
    }

    #[\Override]
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
