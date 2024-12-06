<?php

declare(strict_types=1);

namespace RZ\Roadiz\FontBundle\EventSubscriber;

use League\Flysystem\FilesystemException;
use RZ\Roadiz\FontBundle\Doctrine\EventSubscriber\FontLifeCycleSubscriber;
use RZ\Roadiz\FontBundle\Event\Font\PreUpdatedFontEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Calls font life cycle methods when no data changed according to Doctrine.
 */
final readonly class UpdateFontSubscriber implements EventSubscriberInterface
{
    public function __construct(private FontLifeCycleSubscriber $fontSubscriber)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PreUpdatedFontEvent::class => 'onPreUpdatedFont',
        ];
    }

    /**
     * @throws FilesystemException
     */
    public function onPreUpdatedFont(PreUpdatedFontEvent $event): void
    {
        $font = $event->getFont();
        if (null !== $font) {
            /*
             * Force updating files if uploaded
             * as doctrine won't see any changes.
             */
            $this->fontSubscriber->setFontFilesNames($font);
            $this->fontSubscriber->upload($font);
        }
    }
}
