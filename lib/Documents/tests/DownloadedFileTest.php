<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Tests;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\Documents\DownloadedFile;

class DownloadedFileTest extends TestCase
{
    /**
     * @dataProvider sanitizeFilenameProvider
     */
    public function testSanitizeFilename(string $input, string $expected): void
    {
        $this->assertEquals($expected, DownloadedFile::sanitizeFilename($input));
    }

    public function sanitizeFilenameProvider(): array
    {
        return [
            [
                'Les-Echos_26022015_Les-entrepreneurs-partent-à-lassaut-du-secteur-bancaire.pdf',
                'les_echos_26022015_les_entrepreneurs_partent_a_lassaut_du_secteur_bancaire.pdf',
            ],
            [
                'Les-entrepreneurs-partent-à-lassaut-du-secteur-bancaire.pdf',
                'les_entrepreneurs_partent_a_lassaut_du_secteur_bancaire.pdf',
            ],
            [
                'image.jpg',
                'image.jpg',
            ],
            [
                'image with spaces.jpg',
                'image_with_spaces.jpg',
            ],
            [
                'image/with/slashes.jpg',
                'image_with_slashes.jpg',
            ],
            [
                'image.jpg.webp',
                'image_jpg.webp',
            ],
            [
                'image.png.avif',
                'image_png.avif',
            ],
            [
                'image.png.heif',
                'image_png.heif',
            ],
            [
                'folder/folder.image.jpg.webp',
                'folder_folder_image_jpg.webp',
            ],
            [
                'folder/archive.tar.gz',
                'folder_archive.tar.gz',
            ],
            [
                'folder/archive.tar.xz',
                'folder_archive.tar.xz',
            ],
            [
                'folder/archive.tar.zip',
                'folder_archive.tar.zip',
            ],
            [
                'folder/archive.tar.bz',
                'folder_archive.tar.bz',
            ],
            [
                'folder/archive.tar.bz2',
                'folder_archive.tar.bz2',
            ],
            [
                'folder/archive.tar.tgz',
                'folder_archive.tar.tgz',
            ],
            [
                'folder/archive.tar.7z',
                'folder_archive.tar.7z',
            ],
        ];
    }
}
