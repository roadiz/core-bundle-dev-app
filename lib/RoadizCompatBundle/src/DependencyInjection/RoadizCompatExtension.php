<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\DependencyInjection;

use RZ\Roadiz\CompatBundle\Controller\AppController;
use RZ\Roadiz\CompatBundle\Theme\StaticThemeResolver;
use RZ\Roadiz\CoreBundle\Entity\Theme;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\String\Slugger\AsciiSlugger;

class RoadizCompatExtension extends Extension
{
    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/../config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('roadiz_compat.themes', $config['themes']);
        $container->setDefinition(
            'defaultTranslation',
            (new Definition())
                ->setClass(Translation::class)
                ->setFactory([new Reference(TranslationRepository::class), 'findDefault'])
                ->setShared(true)
                ->setPublic(true)
                ->setDeprecated('roadiz_compat', '2.0.0', '%service_id% service is deprecated, use TranslationRepository instead')
        );

        $this->registerThemes($config, $container);
    }

    private function registerThemes(array $config, ContainerBuilder $container): void
    {
        $frontendThemes = [];

        foreach ($config['themes'] as $index => $themeConfig) {
            $themeSlug = (new AsciiSlugger())->slug($themeConfig['classname'], '_');
            $serviceId = 'roadiz_compat.themes.' . $themeSlug;
            /** @var class-string<AppController> $className */
            $className = $themeConfig['classname'];
            $themeDir = $className::getThemeDir();
            $container->setDefinition(
                $serviceId,
                (new Definition())
                    ->setClass(Theme::class)
                    ->setPublic(true)
                    ->addMethodCall('setId', [$index])
                    ->addMethodCall('setAvailable', [true])
                    ->addMethodCall('setClassName', [$className])
                    ->addMethodCall('setHostname', [$themeConfig['hostname']])
                    ->addMethodCall('setRoutePrefix', [$themeConfig['routePrefix']])
                    ->addMethodCall('setBackendTheme', [false])
                    ->addMethodCall('setStaticTheme', [false])
                    ->addTag('roadiz_compat.theme')
            );
            $frontendThemes[] = new Reference($serviceId);

            // Register asset packages
            $container->setDefinition(
                'roadiz_compat.assets._package.' . $themeSlug,
                (new Definition())
                    ->setClass(PathPackage::class)
                    ->setArguments([
                        'themes/' . $themeDir . '/static',
                        new Reference('assets.empty_version_strategy'),
                        new Reference('assets.context')
                    ])
                    ->addTag('assets.package', [
                        'package' => $themeDir
                    ])
            );

            // Add Twig paths
            $container->getDefinition('roadiz_compat.twig_loader')
                ->addMethodCall('prependPath', [
                    $className::getViewsFolder()
                ])
                ->addMethodCall('prependPath', [
                    $className::getViewsFolder(), $themeDir
                ]);
        }

        if ($container->hasDefinition(StaticThemeResolver::class)) {
            $container->getDefinition(StaticThemeResolver::class)->setArgument('$themes', $frontendThemes);
        }
    }
}
