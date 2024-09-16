<?php
/**
 * Copyright (c) 2017. Ambroise Maupate and Julien Blanchet
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * Except as contained in this notice, the name of the ROADIZ shall not
 * be used in advertising or otherwise to promote the sale, use or other dealings
 * in this Software without prior written authorization from Ambroise Maupate and Julien Blanchet.
 *
 * @file StringHandlerTest.php
 * @author Ambroise Maupate <ambroise@rezo-zero.com>
 */

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\Utils\StringHandler;

/**
 * Class StringHandlerTest
 */
class StringHandlerTest extends TestCase
{

    /**
     * @dataProvider cleanForFilenameProvider
     * @param $input
     * @param $expected
     */
    public function testCleanForFilename($input, $expected)
    {
        $this->assertEquals($expected, StringHandler::cleanForFilename($input));
    }

    public function cleanForFilenameProvider()
    {
        return [
            [
                "Les-Echos_26022015_Les-entrepreneurs-partent-à-lassaut-du-secteur-bancaire.pdf",
                "les_echos_26022015_les_entrepreneurs_partent_a_lassaut_du_secteur_bancaire.pdf"
            ],
            [
                "Les-entrepreneurs-partent-à-lassaut-du-secteur-bancaire.pdf",
                "les_entrepreneurs_partent_a_lassaut_du_secteur_bancaire.pdf"
            ],
            [
                "image.jpg",
                "image.jpg",
            ],
            [
                "image with spaces.jpg",
                "image_with_spaces.jpg",
            ],
            [
                "image/with/slashes.jpg",
                "image_with_slashes.jpg",
            ]
        ];
    }

    /**
     * @dataProvider endsWithProvider
     * @param $input
     * @param $wanted
     * @param $expected
     */
    public function testEndsWith($input, $wanted, $expected)
    {
        $this->assertEquals($expected, StringHandler::endsWith($input, $wanted));
    }

    public function endsWithProvider()
    {
        return [
            ["  ", "Locale", false],
            ["", "Locale", false],
            ["home", "Locale", false],
            ["ocale", "Locale", false],
            ["testPage", "Locale", false],
            ["localePage", "Locale", false],
            ["testLocalePage", "Locale", false],
            ["testPageLocale", "Locale", true],
            ["testPagelocale", "Locale", false],
            ["testPageGateau", "Locale", false],
            ["testPage", "", true],
            ["LocaletestPage", "Locale", false],
        ];
    }

    /**
     * @dataProvider replaceLastProvider
     * @param $input
     * @param $wanted
     * @param $expected
     */
    public function testReplaceLast($input, $wanted, $expected)
    {
        $this->assertEquals($expected, StringHandler::replaceLast($wanted, "", $input));
    }

    /**
     * @return array
     */
    public function replaceLastProvider()
    {
        return [
            ["testPage", "Locale", "testPage"],
            ["localePage", "Locale", "localePage"],
            ["testLocalePage", "Locale", "testPage"],
            ["testPageLocale", "Locale", "testPage"],
            ["testPagelocale", "Locale", "testPagelocale"],
            ["testPageGateau", "Locale", "testPageGateau"],
            ["testPage", "", "testPage"],
            ["LocalePage", "Locale", "Page"],
        ];
    }

    /**
     * @dataProvider removeDiacriticsProvider
     * @param $input
     * @param $expected
     */
    public function testRemoveDiacritics($input, $expected)
    {
        // Assert
        $this->assertEquals($expected, StringHandler::removeDiacritics($input));
    }

    /**
     * @return array
     */
    public function removeDiacriticsProvider()
    {
        return [
            ["à", "a"],
            ["é", "e"],
            ["À", "A"],
            ["É", "E"],
            ["œ", "oe"],
            ["ç", "c"],
            ["__à", "__a"],
            ["--é", "--e"],
            [
                "Les-echos_26022015_Les-entrepreneurs-partent-à-lassaut-du-secteur-bancaire.pdf",
                "Les-echos_26022015_Les-entrepreneurs-partent-a-lassaut-du-secteur-bancaire.pdf"
            ],
        ];
    }

    /**
     * @dataProvider variablizeProvider
     * @param $input
     * @param $expected
     */
    public function testVariablize($input, $expected)
    {
        // Assert
        $this->assertEquals($expected, StringHandler::variablize($input));
    }

    /**
     * @return array
     */
    public function variablizeProvider()
    {
        return [
            ["à", "a"],
            ["é", "e"],
            ["À", "a"],
            ["É", "e"],
            ["œ", "oe"],
            ["ç", "c"],
            ["__à", "a"],
            ["--é", "e"],
            ["Ligula  $* _--Egestas Mattis Nullam$* _  ", "ligula_egestas_mattis_nullam"],
            ["Véèsti buœlum Rïsus+", "veesti_buoelum_risus"],
            ["J'aime les sushis!", "j_aime_les_sushis"],
            ["J’aime les sushis!", "j_aime_les_sushis"],
            ["J'aime les\n sushis!\t\n", "j_aime_les_sushis"],
            ["?header_image", "header_image"],
            ["JAime les_sushis", "j_aime_les_sushis"],
            ["Répétitions publiques à la maison de l'Orchestre", "repetitions_publiques_a_la_maison_de_l_orchestre"],
            ["Ébène", "ebene"],
            ["ébène", "ebene"],
        ];
    }

    /**
     * @dataProvider classifyProvider
     * @param $input
     * @param $expected
     */
    public function testClassify($input, $expected)
    {
        // Assert
        $this->assertEquals($expected, StringHandler::classify($input));
    }

    /**
     * @return array
     */
    public function classifyProvider()
    {
        return [
            ["Ligula  $* _--Egestas Mattis Nullam", "LigulaEgestasMattisNullam"],
            ["Véèsti buœlum Rïsus", "VeestiBuoelumRisus"],
            ["J'aime les sushis", "JAimeLesSushis"],
            ["header_image", "HeaderImage"],
            ["JAime les_sushis", "JAimeLesSushis"],
        ];
    }

    /**
     * @dataProvider camelCaseProvider
     * @param $input
     * @param $expected
     */
    public function testCamelCase($input, $expected)
    {
        // Assert
        $this->assertEquals($expected, StringHandler::camelcase($input));
    }

    /**
     * @return array
     */
    public function camelCaseProvider()
    {
        return [
            ["Ligula  $* _--Egestas Mattis Nullam", "ligulaEgestasMattisNullam"],
            ["Véèsti buœlum Rïsus", "veestiBuoelumRisus"],
            ["J'aime les sushis", "jAimeLesSushis"],
            ["header_image", "headerImage"],
            ["JAime les_sushis", "jAimeLesSushis"],
        ];
    }

    /**
     * @dataProvider slugifyProvider
     * @param $input
     * @param $expected
     */
    public function testSlugify($input, $expected)
    {
        // Assert
        $this->assertEquals($expected, StringHandler::slugify($input));
    }

    /**
     * @return array
     */
    public function slugifyProvider()
    {
        return [
            ["Ligula  $* _--Egestas Mattis Nullam$* _  ", "ligula-egestas-mattis-nullam"],
            ["Véèsti buœlum Rïsus+", "veesti-buoelum-risus"],
            ["veesti-buoelum-risus", "veesti-buoelum-risus"],
            ["J'aime les sushis!", "j-aime-les-sushis"],
            ["J’aime les sushis!", "j-aime-les-sushis"],
            ["J'aime les\n sushis!\t\n", "j-aime-les-sushis"],
            ["?header_image", "header-image"],
            ["JAime les_sushis", "jaime-les-sushis"],
            ["Ébène", "ebene"],
            ["ébène", "ebene"],
            ["Page1 1", "page1-1"],
            ["Page3", "page3"],
            ["Page 3", "page-3"],
            ["Page 3 3", "page-3-3"],
            ["12 Page 3 3", "12-page-3-3"],
            ["straßburg", "strassburg"]
        ];
    }

    /**
     * @dataProvider encodeWithSecretProvider
     * @param $input
     * @param $secret
     */
    public function testEncodeWithSecret($input, $secret)
    {
        $code = StringHandler::encodeWithSecret($input, $secret);

        // Assert
        $this->assertEquals($input, StringHandler::decodeWithSecret($code, $secret));
    }

    /**
     * @return array
     */
    public function encodeWithSecretProvider()
    {
        return [
            ["Ligula  $* _--Egestas Mattis Nullam", "Commodo Pellentesque Sem Fusce Quam"],
            ["Véèsti buœlum Rïsus ", "  change#this#secret#very#important"],
            ["J'aime les sushis  ", " Fringilla Vulputate Dolor Inceptos"],
            ["au   " . PHP_EOL . "ietaui.\\eauie@auietsrt.trr", "Sit Vestibulum Dolor Ullamcorper Aenean"],
            ["JAime les_sushis", "Sit Vestibulum Dolor"],
        ];
    }

    /**
     * @dataProvider encodeWithSecretNoSaltProvider
     * @param $input
     * @param $secret
     */
    public function testEncodeWithSecretNoSalt($input, $secret)
    {
        $this->expectException('\\InvalidArgumentException');

        $code = StringHandler::encodeWithSecret($input, $secret);

        // Assert
        $this->assertEquals($input, StringHandler::decodeWithSecret($code, $secret));
    }

    /**
     * @return array
     */
    public function encodeWithSecretNoSaltProvider()
    {
        return [
            ["Ligula  $* _--Egestas Mattis Nullam", ""],
            ["Véèsti buœlum Rïsus ", "  "],
            ["J'aime les sushis  ", "  "],
            ["auietauieauie@auietsrt.trr", PHP_EOL],
        ];
    }
}
