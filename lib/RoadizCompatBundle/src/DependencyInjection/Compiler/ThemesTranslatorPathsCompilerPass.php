<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\DependencyInjection\Compiler;

use RZ\Roadiz\CompatBundle\Controller\AppController;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

class ThemesTranslatorPathsCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('translator.default')) {
            $this->registerThemeTranslatorResources($container);
        }
    }

    private function registerThemeTranslatorResources(ContainerBuilder $container): void
    {
        /** @var string $projectDir */
        $projectDir = $container->getParameter('kernel.project_dir');
        /** @var \Iterator|array $themesConfig */
        $themesConfig = $container->getParameter('roadiz_compat.themes');
        $translator = $container->findDefinition('translator.default');
        $options = [
            'resource_files' => [],
            'scanned_directories' => [],
            'cache_vary' => [
                'scanned_directories' => [],
            ],
        ];

        foreach ($themesConfig as $themeConfig) {
            /** @var class-string<AppController> $className */
            $className = $themeConfig['classname'];

            // add translations paths
            $translationFolder = $className::getTranslationsFolder();
            if (file_exists($translationFolder)) {
                $files = [];
                $finder = Finder::create()
                    ->followLinks()
                    ->files()
                    ->filter(function (\SplFileInfo $file) {
                        return 2 <= \mb_substr_count($file->getBasename(), '.') &&
                            \preg_match('/\.\w+$/', $file->getBasename());
                    })
                    ->in($translationFolder)
                    ->sortByName()
                ;
                foreach ($finder as $file) {
                    $fileNameParts = explode('.', basename((string) $file));
                    $locale = $fileNameParts[\count($fileNameParts) - 2];
                    if (!isset($files[$locale])) {
                        $files[$locale] = [];
                    }

                    $files[$locale][] = (string) $file;
                }
                $options = array_merge_recursive(
                    $options,
                    [
                        'resource_files' => $files,
                        'scanned_directories' => $scannedDirectories = [$translationFolder],
                        'cache_vary' => [
                            'scanned_directories' => array_map(static function (string $dir) use ($projectDir): string {
                                return str_starts_with($dir, $projectDir . '/') ? \mb_substr($dir, 1 + \mb_strlen($projectDir)) : $dir;
                            }, $scannedDirectories),
                        ],
                    ]
                );
            }
        }
        $options = array_merge_recursive(
            $translator->getArgument(4),
            $options
        );

        $translator->replaceArgument(4, $options);
    }
}
