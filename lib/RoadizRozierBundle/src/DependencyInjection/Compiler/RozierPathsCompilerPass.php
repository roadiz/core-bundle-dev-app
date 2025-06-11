<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\DependencyInjection\Compiler;

use Symfony\Component\Asset\PathPackage;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RozierPathsCompilerPass implements CompilerPassInterface
{
    #[\Override]
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
            'roadiz_rozier.assets._package.'.$name,
            (new Definition())
                ->setClass(PathPackage::class)
                ->setArguments([
                    'themes/'.$name.'/static',
                    new Reference('assets.empty_version_strategy'),
                    new Reference('assets.context'),
                ])
                ->addTag('assets.package', [
                    'package' => $name,
                ])
        );
    }
}
