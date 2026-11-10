<?php

declare(strict_types=1);

namespace App\Tests\Rozier;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\RozierBundle\Vite\JsonManifestResolver;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Vite entries resolved into the script, style and preload tags of the backoffice layouts.
 */
final class JsonManifestResolverTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir().'/rozier-manifest-'.bin2hex(random_bytes(4));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir.'/*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->dir);
    }

    public function testJavascriptEntryResolvesToItsScriptAndImportedStyles(): void
    {
        $resolver = $this->createResolver([
            'app/main.js' => [
                'file' => 'main-abc.js',
                'name' => 'main',
                'src' => 'app/main.js',
                'isEntry' => true,
                'css' => ['main-def.css'],
            ],
        ]);

        $this->assertSame(['/bundles/roadizrozier/main-abc.js'], $resolver->getEntrypointScriptFiles('main'));
        $this->assertSame(['/bundles/roadizrozier/main-def.css'], $resolver->getEntrypointCssFiles('main'));
    }

    public function testBuiltCssEntryResolvesToAStylesheetFoundByItsNames(): void
    {
        // Vite writes no `name` for a CSS entry, only `names`
        $resolver = $this->createResolver([
            'app/assets/css/main.css' => [
                'file' => 'shared-abc.css',
                'names' => ['shared.css'],
                'src' => 'app/assets/css/main.css',
                'isEntry' => true,
            ],
        ]);

        $this->assertSame(['/bundles/roadizrozier/shared-abc.css'], $resolver->getEntrypointCssFiles('shared'));
        $this->assertSame([], $resolver->getEntrypointScriptFiles('shared'));
    }

    public function testDevServerCssEntryResolvesToAStylesheet(): void
    {
        $this->writeManifest('manifest.json', []);
        $resolver = $this->createResolver([
            'app/assets/css/main.css' => [
                'file' => 'http://localhost:5173/app/assets/css/main.css',
                'name' => 'shared',
                'src' => 'app/assets/css/main.css',
                'isEntry' => true,
                'imports' => [],
                'assets' => [],
            ],
            'app/main.js' => [
                'file' => 'http://localhost:5173/app/main.js',
                'name' => 'main',
                'src' => 'app/main.js',
                'isEntry' => true,
                'imports' => [],
                'assets' => [],
            ],
        ], 'manifest.dev.json', debug: true);

        $this->assertSame(['http://localhost:5173/app/assets/css/main.css'], $resolver->getEntrypointCssFiles('shared'));
        $this->assertSame([], $resolver->getEntrypointScriptFiles('shared'));
        $this->assertSame(['http://localhost:5173/app/main.js'], $resolver->getEntrypointScriptFiles('main'));
        $this->assertSame([], $resolver->getEntrypointCssFiles('main'));
    }

    /**
     * @param array<string, array<string, mixed>> $manifest
     */
    private function createResolver(array $manifest, string $fileName = 'manifest.json', bool $debug = false): JsonManifestResolver
    {
        $this->writeManifest($fileName, $manifest);

        return new JsonManifestResolver(
            $this->dir.'/manifest.json',
            new Packages(new Package(new EmptyVersionStrategy())),
            new ArrayAdapter(),
            $debug,
        );
    }

    /**
     * @param array<string, array<string, mixed>> $manifest
     */
    private function writeManifest(string $fileName, array $manifest): void
    {
        file_put_contents($this->dir.'/'.$fileName, json_encode($manifest, JSON_THROW_ON_ERROR));
    }
}
