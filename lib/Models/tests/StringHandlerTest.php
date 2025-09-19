<?php

declare(strict_types=1);

namespace RZ\Roadiz\Tests;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\Utils\StringHandler;

class StringHandlerTest extends TestCase
{
    /**
     * @dataProvider cleanForFilenameProvider
     */
    public function testCleanForFilename(string $input, string $expected): void
    {
        $this->assertEquals($expected, StringHandler::cleanForFilename($input));
    }

    public function cleanForFilenameProvider(): array
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

    /**
     * @dataProvider endsWithProvider
     */
    public function testEndsWith(string $input, string $wanted, bool $expected): void
    {
        $this->assertEquals($expected, StringHandler::endsWith($input, $wanted));
    }

    public function endsWithProvider(): array
    {
        return [
            ['  ', 'Locale', false],
            ['', 'Locale', false],
            ['home', 'Locale', false],
            ['ocale', 'Locale', false],
            ['testPage', 'Locale', false],
            ['localePage', 'Locale', false],
            ['testLocalePage', 'Locale', false],
            ['testPageLocale', 'Locale', true],
            ['testPagelocale', 'Locale', false],
            ['testPageGateau', 'Locale', false],
            ['testPage', '', true],
            ['LocaletestPage', 'Locale', false],
        ];
    }

    /**
     * @dataProvider replaceLastProvider
     */
    public function testReplaceLast(string $input, string $wanted, string $expected): void
    {
        $this->assertEquals($expected, StringHandler::replaceLast($wanted, '', $input));
    }

    public function replaceLastProvider(): array
    {
        return [
            ['testPage', 'Locale', 'testPage'],
            ['localePage', 'Locale', 'localePage'],
            ['testLocalePage', 'Locale', 'testPage'],
            ['testPageLocale', 'Locale', 'testPage'],
            ['testPagelocale', 'Locale', 'testPagelocale'],
            ['testPageGateau', 'Locale', 'testPageGateau'],
            ['testPage', '', 'testPage'],
            ['LocalePage', 'Locale', 'Page'],
        ];
    }

    /**
     * @dataProvider removeDiacriticsProvider
     */
    public function testRemoveDiacritics(string $input, string $expected): void
    {
        // Assert
        $this->assertEquals($expected, StringHandler::removeDiacritics($input));
    }

    public function removeDiacriticsProvider(): array
    {
        return [
            ['à', 'a'],
            ['é', 'e'],
            ['À', 'A'],
            ['É', 'E'],
            ['œ', 'oe'],
            ['ç', 'c'],
            ['__à', '__a'],
            ['--é', '--e'],
            [
                'Les-echos_26022015_Les-entrepreneurs-partent-à-lassaut-du-secteur-bancaire.pdf',
                'Les-echos_26022015_Les-entrepreneurs-partent-a-lassaut-du-secteur-bancaire.pdf',
            ],
        ];
    }

    /**
     * @dataProvider variablizeProvider
     */
    public function testVariablize(string $input, string $expected): void
    {
        // Assert
        $this->assertEquals($expected, StringHandler::variablize($input));
    }

    public function variablizeProvider(): array
    {
        return [
            ['à', 'a'],
            ['é', 'e'],
            ['À', 'a'],
            ['É', 'e'],
            ['œ', 'oe'],
            ['ç', 'c'],
            ['__à', 'a'],
            ['--é', 'e'],
            ['Ligula  $* _--Egestas Mattis Nullam$* _  ', 'ligula_egestas_mattis_nullam'],
            ['Véèsti buœlum Rïsus+', 'veesti_buoelum_risus'],
            ["J'aime les sushis!", 'j_aime_les_sushis'],
            ['J’aime les sushis!', 'j_aime_les_sushis'],
            ["J'aime les\n sushis!\t\n", 'j_aime_les_sushis'],
            ['?header_image', 'header_image'],
            ['JAime les_sushis', 'j_aime_les_sushis'],
            ['Ébène', 'ebene'],
            ['ébène', 'ebene'],
        ];
    }

    /**
     * @dataProvider classifyProvider
     */
    public function testClassify(string $input, string $expected): void
    {
        // Assert
        $this->assertEquals($expected, StringHandler::classify($input));
    }

    public function classifyProvider(): array
    {
        return [
            ['Ligula  $* _--Egestas Mattis Nullam', 'LigulaEgestasMattisNullam'],
            ['Véèsti buœlum Rïsus', 'VeestiBuoelumRisus'],
            ["J'aime les sushis", 'JAimeLesSushis'],
            ['header_image', 'HeaderImage'],
            ['JAime les_sushis', 'JAimeLesSushis'],
        ];
    }

    /**
     * @dataProvider camelCaseProvider
     */
    public function testCamelCase(string $input, string $expected): void
    {
        // Assert
        $this->assertEquals($expected, StringHandler::camelcase($input));
    }

    public function camelCaseProvider(): array
    {
        return [
            ['Ligula  $* _--Egestas Mattis Nullam', 'ligulaEgestasMattisNullam'],
            ['Véèsti buœlum Rïsus', 'veestiBuoelumRisus'],
            ["J'aime les sushis", 'jAimeLesSushis'],
            ['header_image', 'headerImage'],
            ['JAime les_sushis', 'jAimeLesSushis'],
        ];
    }

    /**
     * @dataProvider slugifyProvider
     */
    public function testSlugify(string $input, string $expected): void
    {
        // Assert
        $this->assertEquals($expected, StringHandler::slugify($input));
    }

    public function slugifyProvider(): array
    {
        return [
            ['Ligula  $* _--Egestas Mattis Nullam$* _  ', 'ligula-egestas-mattis-nullam'],
            ['Véèsti buœlum Rïsus+', 'veesti-buoelum-risus'],
            ['veesti-buoelum-risus', 'veesti-buoelum-risus'],
            ["J'aime les sushis!", 'j-aime-les-sushis'],
            ['J’aime les sushis!', 'j-aime-les-sushis'],
            ["J'aime les\n sushis!\t\n", 'j-aime-les-sushis'],
            ['?header_image', 'header-image'],
            ['JAime les_sushis', 'jaime-les-sushis'],
            ['Ébène', 'ebene'],
            ['ébène', 'ebene'],
            ['Page1 1', 'page1-1'],
            ['Page3', 'page3'],
            ['Page 3', 'page-3'],
            ['Page 3 3', 'page-3-3'],
            ['12 Page 3 3', '12-page-3-3'],
            ['straßburg', 'strassburg'],
        ];
    }

    /**
     * @dataProvider encodeWithSecretProvider
     */
    public function testEncodeWithSecret(string $input, string $secret): void
    {
        $code = StringHandler::encodeWithSecret($input, $secret);

        // Assert
        $this->assertEquals($input, StringHandler::decodeWithSecret($code, $secret));
    }

    public function encodeWithSecretProvider(): array
    {
        return [
            ['Ligula  $* _--Egestas Mattis Nullam', 'Commodo Pellentesque Sem Fusce Quam'],
            ['Véèsti buœlum Rïsus ', '  change#this#secret#very#important'],
            ["J'aime les sushis  ", ' Fringilla Vulputate Dolor Inceptos'],
            ['au   '.PHP_EOL.'ietaui.\\eauie@auietsrt.trr', 'Sit Vestibulum Dolor Ullamcorper Aenean'],
            ['JAime les_sushis', 'Sit Vestibulum Dolor'],
        ];
    }

    /**
     * @dataProvider encodeWithSecretNoSaltProvider
     */
    public function testEncodeWithSecretNoSalt(string $input, string $secret): void
    {
        $this->expectException('\\InvalidArgumentException');

        $code = StringHandler::encodeWithSecret($input, $secret);

        // Assert
        $this->assertEquals($input, StringHandler::decodeWithSecret($code, $secret));
    }

    public function encodeWithSecretNoSaltProvider(): array
    {
        return [
            ['Ligula  $* _--Egestas Mattis Nullam', ''],
            ['Véèsti buœlum Rïsus ', '  '],
            ["J'aime les sushis  ", '  '],
            ['auietauieauie@auietsrt.trr', PHP_EOL],
        ];
    }
}
