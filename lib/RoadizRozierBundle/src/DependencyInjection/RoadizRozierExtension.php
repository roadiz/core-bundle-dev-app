<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\DependencyInjection;

use RZ\Roadiz\OpenId\Discovery;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RoadizRozierExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $projectDir = $container->getParameter('kernel.project_dir');
        if (!\is_string($projectDir)) {
            throw new \RuntimeException('kernel.project_dir parameter is not a string.');
        }
        $container->setParameter('roadiz_rozier.backoffice_menu_configuration', $config['entries']);
        $container->setParameter('roadiz_rozier.node_form.class', $config['node_form']);
        $container->setParameter('roadiz_rozier.add_node_form.class', $config['add_node_form']);
        $container->setParameter(
            'roadiz_rozier.theme_dir',
            $projectDir.DIRECTORY_SEPARATOR.trim($config['theme_dir'], "/ \t\n\r\0\x0B")
        );

        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__).'/../config'));
        $loader->load('services.yaml');

        $this->registerOpenId($config, $container);
    }

    private function registerOpenId(array $config, ContainerBuilder $container): void
    {
        $container->setParameter('roadiz_rozier.open_id.verify_user_info', $config['open_id']['verify_user_info']);
        $container->setParameter('roadiz_rozier.open_id.force_ssl_on_redirect_uri', $config['open_id']['force_ssl_on_redirect_uri']);
        $container->setParameter('roadiz_rozier.open_id.discovery_url', $config['open_id']['discovery_url']);
        $container->setParameter('roadiz_rozier.open_id.hosted_domain', $config['open_id']['hosted_domain']);
        $container->setParameter('roadiz_rozier.open_id.oauth_client_id', $config['open_id']['oauth_client_id']);
        $container->setParameter('roadiz_rozier.open_id.oauth_client_secret', $config['open_id']['oauth_client_secret']);
        $container->setParameter('roadiz_rozier.open_id.requires_local_user', $config['open_id']['requires_local_user']);
        $container->setParameter('roadiz_rozier.open_id.openid_username_claim', $config['open_id']['openid_username_claim']);
        $container->setParameter('roadiz_rozier.open_id.scopes', $config['open_id']['scopes'] ?? []);
        $container->setParameter('roadiz_rozier.open_id.granted_roles', $config['open_id']['granted_roles'] ?? []);

        // Do not test URL here, as DotEnv could not be loaded yet.
        if (!empty($config['open_id']['discovery_url'])) {
            /*
             * Register OpenID discovery service only when discovery URL is set.
             */
            $container->setDefinition(
                'roadiz_rozier.open_id.discovery',
                (new Definition())
                    ->setClass(Discovery::class)
                    ->setPublic(true)
                    ->setArguments([
                        $config['open_id']['discovery_url'],
                        new Reference(\Psr\Cache\CacheItemPoolInterface::class),
                        new Reference(HttpClientInterface::class),
                        new Reference(\Psr\Log\LoggerInterface::class),
                    ])
            );
        }

        $container->setDefinition(
            'roadiz_rozier.open_id.jwt_configuration_factory',
            (new Definition())
                ->setClass(\RZ\Roadiz\OpenId\OpenIdJwtConfigurationFactory::class)
                ->setPublic(true)
                ->setArguments([
                    new Reference('roadiz_rozier.open_id.discovery', ContainerInterface::NULL_ON_INVALID_REFERENCE),
                    new Reference(HttpClientInterface::class),
                    $config['open_id']['hosted_domain'],
                    $config['open_id']['oauth_client_id'],
                    $config['open_id']['verify_user_info'],
                ])
        );

        /*
         * Always register OpenID authenticator to be able to use it in firewall.
         */
        $container->setDefinition(
            'roadiz_rozier.open_id.authenticator',
            (new Definition())
                ->setClass(\RZ\Roadiz\OpenId\Authentication\OpenIdAuthenticator::class)
                ->setPublic(true)
                ->setArguments([
                    new Reference('security.http_utils'),
                    new Reference('roadiz_rozier.open_id.discovery', ContainerInterface::NULL_ON_INVALID_REFERENCE),
                    new Reference(\RZ\Roadiz\OpenId\Authentication\Provider\ChainJwtRoleStrategy::class),
                    new Reference('roadiz_rozier.open_id.jwt_configuration_factory'),
                    new Reference(\Symfony\Component\Routing\Generator\UrlGeneratorInterface::class),
                    new Reference(HttpClientInterface::class),
                    'loginPage',
                    'adminHomePage',
                    $config['open_id']['oauth_client_id'],
                    $config['open_id']['oauth_client_secret'],
                    $config['open_id']['force_ssl_on_redirect_uri'],
                    $config['open_id']['requires_local_user'],
                    $config['open_id']['openid_username_claim'],
                    '_target_path',
                    $config['open_id']['granted_roles'],
                ])
        );
    }
}
