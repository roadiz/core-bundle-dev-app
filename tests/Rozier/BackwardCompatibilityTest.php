<?php

declare(strict_types=1);

namespace App\Tests\Rozier;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\RozierBundle\Breadcrumbs\BreadcrumbsItem;
use RZ\Roadiz\RozierBundle\DependencyInjection\Configuration;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\Config\Definition\Processor;

/**
 * Extension points documented in 2.7 that 2.8 changed keep working, deprecated (ADR 0002).
 */
final class BackwardCompatibilityTest extends TestCase
{
    use ExpectDeprecationTrait;

    /**
     * @group legacy
     */
    public function testUikitMenuIconsFallBackToAGenericIcon(): void
    {
        $this->expectDeprecation('Since roadiz/rozier-bundle 2.8: The "uk-icon-cube" menu icon of "roadiz_rozier.entries" is deprecated%s');
        $this->expectDeprecation('Since roadiz/rozier-bundle 2.8: The "uk-icon-paper-plane" menu icon of "roadiz_rozier.entries" is deprecated%s');

        $config = $this->processEntries([
            'customAdmin' => [
                'name' => 'customAdmin',
                'icon' => 'uk-icon-cube',
                'subentries' => [
                    'articles' => ['name' => 'articles', 'icon' => 'uk-icon-paper-plane'],
                ],
            ],
        ]);

        $this->assertSame('rz-icon-ri--layout-grid-line', $config['entries']['customAdmin']['icon']);
        $this->assertSame('rz-icon-ri--layout-grid-line', $config['entries']['customAdmin']['subentries']['articles']['icon']);
    }

    public function testCurrentMenuIconsAreKept(): void
    {
        $config = $this->processEntries([
            'articles' => ['name' => 'articles', 'icon' => 'rz-icon-ri--mail-send-line'],
        ]);

        $this->assertSame('rz-icon-ri--mail-send-line', $config['entries']['articles']['icon']);
    }

    /**
     * @group legacy
     */
    public function testBreadcrumbsItemAcceptsTheRemovedArguments(): void
    {
        $this->expectDeprecation('Since roadiz/rozier-bundle 2.8: Passing "$home" or "$enabled" to "%s" is deprecated%s');

        $item = new BreadcrumbsItem('Articles', '/rz-admin/articles', true);

        $this->assertSame('Articles', $item->label);
        $this->assertSame('/rz-admin/articles', $item->url);
    }

    /**
     * @param array<string, mixed> $entries
     *
     * @return array<string, mixed>
     */
    private function processEntries(array $entries): array
    {
        return (new Processor())->processConfiguration(new Configuration(), [['entries' => $entries]]);
    }
}
