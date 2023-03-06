<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Subscriber;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Event\Document\DocumentTranslationIndexingEvent;
use RZ\Roadiz\CoreBundle\SearchEngine\AbstractSolarium;
use RZ\Roadiz\CoreBundle\SearchEngine\SolariumDocumentTranslation;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DefaultDocumentTranslationIndexingSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            DocumentTranslationIndexingEvent::class => ['onIndexing', 1000],
        ];
    }

    public function onIndexing(DocumentTranslationIndexingEvent $event): void
    {
        $documentTranslation = $event->getDocumentTranslation();
        $assoc = $event->getAssociations();
        $collection = [];
        $document = $documentTranslation->getDocument();

        $assoc[AbstractSolarium::TYPE_DISCRIMINATOR] = SolariumDocumentTranslation::DOCUMENT_TYPE;
        $assoc[SolariumDocumentTranslation::IDENTIFIER_KEY] = $documentTranslation->getId();
        if ($document instanceof Document) {
            $assoc['document_id_i'] = $document->getId();
            $assoc['created_at_dt'] = $document->getCreatedAt()
                ->setTimezone(new \DateTimeZone('UTC'))
                ->format('Y-m-d\TH:i:s\Z');
            ;
            $assoc['updated_at_dt'] = $document->getUpdatedAt()
                ->setTimezone(new \DateTimeZone('UTC'))
                ->format('Y-m-d\TH:i:s\Z');
            ;

            $copyrightValidSince = $document->getCopyrightValidSince() ?? new \DateTime('1970-01-01 00:00:00');
            $copyrightValidUntil = $document->getCopyrightValidUntil() ?? new \DateTime('9999-12-31 23:59:59');
            $assoc['copyright_valid_since_dt'] = $copyrightValidSince
                ->setTimezone(new \DateTimeZone('UTC'))
                ->format('Y-m-d\TH:i:s\Z');
            ;
            $assoc['copyright_valid_until_dt'] = $copyrightValidUntil
                ->setTimezone(new \DateTimeZone('UTC'))
                ->format('Y-m-d\TH:i:s\Z');
            ;
        }
        $assoc['filename_s'] = $document->getFilename();
        $assoc['mime_type_s'] = $document->getMimeType();

        $translation = $documentTranslation->getTranslation();
        $locale = $translation->getLocale();
        $assoc['locale_s'] = $locale;
        $lang = \Locale::getPrimaryLanguage($locale);

        /*
         * Use locale to create field name
         * with right language
         */
        $suffix = '_t';
        if (in_array($lang, SolariumDocumentTranslation::$availableLocalizedTextFields)) {
            $suffix = '_txt_' . $lang;
        }

        $assoc['title'] = $documentTranslation->getName();
        $assoc['title' . $suffix] = $documentTranslation->getName();

        /*
         * Remove ctrl characters
         */
        $description = $event->getSolariumDocument()->cleanTextContent($documentTranslation->getDescription());
        $assoc['description' . $suffix] = $description;

        $assoc['copyright' . $suffix] = $documentTranslation->getCopyright();

        $collection[] = $assoc['title'];
        $collection[] = $assoc['description' . $suffix];
        $collection[] = $assoc['copyright' . $suffix];

        /*
         * `tags_txt` Must store only public, visible and user-searchable content.
         */
        $visibleFolders = $document->getFolders()->filter(function (Folder $folder) {
            return $folder->isVisible();
        })->toArray();
        $visibleFolderNames = [];
        /** @var Folder $folder */
        foreach ($visibleFolders as $folder) {
            $visibleFolderNames[] = $folder->getFolderName();
            if ($fTrans = $folder->getTranslatedFoldersByTranslation($translation)->first()) {
                $visibleFolderNames[] = $fTrans->getName();
            }
        }
        $visibleFolderNames = array_filter(array_unique($visibleFolderNames));
        // Use tags_txt to be compatible with other data types
        $assoc['tags_txt'] = $visibleFolderNames;
        // Compile all tags names into a single localized text field.
        $assoc['tags_txt_' . $lang] = implode(' ', $visibleFolderNames);

        /*
         * `all_tags_txt` can store all folders, even technical one, this fields should not user searchable.
         */
        $allFolders = $document->getFolders();
        $allFolderNames = [];
        /** @var Folder $folder */
        foreach ($allFolders as $folder) {
            $allFolderNames[] = $folder->getFolderName();
        }
        // Use all_tags_txt to be compatible with other data types
        $assoc['all_tags_txt'] = array_filter(array_unique($allFolderNames));

        /*
         * Collect data in a single field
         * for global search
         */
        $assoc['collection_txt'] = $collection;
        // Compile all text content into a single localized text field.
        $assoc['collection_txt_' . $lang] = implode(PHP_EOL, $collection);
        $event->setAssociations($assoc);
    }
}
