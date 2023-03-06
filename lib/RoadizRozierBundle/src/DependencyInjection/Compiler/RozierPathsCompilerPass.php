<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\DependencyInjection\Compiler;

use RZ\Roadiz\RozierBundle\DependencyInjection\Configuration;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;

class RozierPathsCompilerPass implements CompilerPassInterface
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
        $projectDir = $container->getParameter('kernel.project_dir');
        $themeDir = $container->getParameter('roadiz_rozier.theme_dir');

        if (!is_string($projectDir)) {
            throw new \RuntimeException('kernel.project_dir is not a valid string');
        }
        if (!is_string($themeDir)) {
            throw new \RuntimeException('roadiz_rozier.theme_dir is not a valid string');
        }

        /*
         * add Assets package '%kernel.project_dir%/themes/Rozier/static'
         */
        $name = 'Rozier';
        // Register asset packages
        $container->setDefinition(
            'roadiz_rozier.assets._package.' . $name,
            (new Definition())
                ->setClass(PathPackage::class)
                ->setArguments([
                    'themes/' . $name . '/static',
                    new Reference('assets.empty_version_strategy'),
                    new Reference('assets.context')
                ])
                ->addTag('assets.package', [
                    'package' => $name
                ])
        );

        /*
         * add translations paths
         */
        $translationFolder = realpath($themeDir . '/Resources/translations');

        if (false === $translationFolder || !file_exists($translationFolder)) {
            throw new \RuntimeException($themeDir . '/Resources/translations' . ' is not a valid directory');
        }

        if ($container->hasDefinition('translator.default')) {
            $translator = $container->findDefinition('translator.default');
            $files = [];
            $finder = Finder::create()
                ->followLinks()
                ->files()
                ->filter(function (\SplFileInfo $file) {
                    return 2 <= substr_count($file->getBasename(), '.') &&
                        preg_match('/\.\w+$/', $file->getBasename());
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
            /** @var array $options */
            $options = $translator->getArgument(4);

            $options = array_merge_recursive(
                $options,
                [
                    'resource_files' => $files,
                    'scanned_directories' => $scannedDirectories = [$translationFolder],
                    'cache_vary' => [
                        'scanned_directories' => array_map(static function (string $dir) use ($projectDir): string {
                            return str_starts_with($dir, $projectDir . '/') ? substr($dir, 1 + \strlen($projectDir)) : $dir;
                        }, $scannedDirectories),
                    ],
                ]
            );

            $translator->replaceArgument(4, $options);
        }
    }
}
