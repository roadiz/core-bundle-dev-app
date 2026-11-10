<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Tests;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\Documents\ArrayDocumentFinder;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\SimpleDocument;

final class ArrayDocumentFinderTest extends TestCase
{
    public function testFindVideosWithFilenameReturnsOnlyVideos(): void
    {
        $this->assertSame(
            ['file.mp4', 'file.m4v', 'file.webm'],
            $this->getFilenames($this->getDocumentFinder()->findVideosWithFilename('file.mp4'))
        );
    }

    public function testFindAudiosWithFilenameReturnsOnlyAudios(): void
    {
        $this->assertSame(
            ['file.ogg', 'file.oga', 'file.opus', 'file.flac', 'file.weba'],
            $this->getFilenames($this->getDocumentFinder()->findAudiosWithFilename('file.mp3'))
        );
    }

    /**
     * @param iterable<DocumentInterface> $documents
     *
     * @return list<string>
     */
    private function getFilenames(iterable $documents): array
    {
        $filenames = [];
        foreach ($documents as $document) {
            $filenames[] = $document->getFilename();
        }

        return $filenames;
    }

    private function getDocumentFinder(): ArrayDocumentFinder
    {
        $finder = new ArrayDocumentFinder();
        foreach ([
            'file.mp4' => 'video/mp4',
            'file.m4v' => 'video/x-m4v',
            'file.webm' => 'video/webm',
            'file.avi' => 'video/x-msvideo',
            'file.ogg' => 'audio/ogg',
            'file.oga' => 'audio/ogg',
            'file.opus' => 'audio/opus',
            'file.flac' => 'audio/x-flac',
            'file.weba' => 'audio/webm',
            'file.jpg' => 'image/jpeg',
        ] as $filename => $mimeType) {
            $finder->addDocument(
                (new SimpleDocument())
                    ->setFilename($filename)
                    ->setFolder('folder')
                    ->setMimeType($mimeType)
            );
        }

        return $finder;
    }
}
