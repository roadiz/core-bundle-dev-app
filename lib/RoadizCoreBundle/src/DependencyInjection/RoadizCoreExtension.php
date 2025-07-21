<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection;

use League\CommonMark\Environment\Environment;
use League\CommonMark\MarkdownConverter;
use RZ\Roadiz\CoreBundle\Cache\CloudflareProxyCache;
use RZ\Roadiz\CoreBundle\Cache\ReverseProxyCache;
use RZ\Roadiz\CoreBundle\Cache\ReverseProxyCacheLocator;
use RZ\Roadiz\CoreBundle\DataCollector\SolariumLogger;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesCustomForms;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;
use RZ\Roadiz\CoreBundle\Webhook\Message\GenericJsonPostMessageInterface;
use RZ\Roadiz\CoreBundle\Webhook\Message\GitlabPipelineTriggerMessageInterface;
use RZ\Roadiz\CoreBundle\Webhook\Message\NetlifyBuildHookMessageInterface;
use RZ\Roadiz\Markdown\CommonMark;
use RZ\Roadiz\Markdown\MarkdownInterface;
use Solarium\Core\Client\Adapter\Curl;
use Solarium\Core\Client\Client;
use Solarium\Core\Client\Endpoint;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RoadizCoreExtension extends Extension
{
    public function getAlias(): string
    {
        return 'roadiz_core';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__).'/../config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('roadiz_core.app_namespace', $config['appNamespace']);
        $container->setParameter('roadiz_core.app_version', $config['appVersion']);
        $container->setParameter('roadiz_core.help_external_url', $config['helpExternalUrl']);
        $container->setParameter('roadiz_core.use_gravatar', $config['useGravatar']);
        $container->setParameter('roadiz_core.use_email_reply_to', $config['useEmailReplyTo']);
        $container->setParameter('roadiz_core.health_check_token', $config['healthCheckToken']);
        $container->setParameter('roadiz_core.inheritance_type', $config['inheritance']['type']);
        $container->setParameter('roadiz_core.max_versions_showed', $config['maxVersionsShowed']);
        $container->setParameter('roadiz_core.static_domain_name', $config['staticDomainName'] ?? '');
        $container->setParameter('roadiz_core.default_node_source_controller', $config['defaultNodeSourceController']);
        $container->setParameter('roadiz_core.use_native_json_column_type', $config['useNativeJsonColumnType']);
        $container->setParameter('roadiz_core.use_typed_node_names', $config['useTypedNodeNames']);
        $container->setParameter('roadiz_core.hide_roadiz_version', $config['hideRoadizVersion']);
        $container->setParameter('roadiz_core.use_accept_language_header', $config['useAcceptLanguageHeader']);
        $container->setParameter('roadiz_core.web_response_class', $config['webResponseClass']);
        $container->setParameter('roadiz_core.preview_required_role_name', $config['previewRequiredRoleName']);

        /*
         * Assets config
         */
        if (extension_loaded('gd')) {
            $gd_infos = gd_info();
            $container->setParameter('roadiz_core.assets_processing.supports_webp', (bool) $gd_infos['WebP Support']);
        } else {
            $container->setParameter('roadiz_core.assets_processing.supports_webp', false);
        }

        /** @var string $projectDir */
        $projectDir = $container->getParameter('kernel.project_dir');
        $container->setParameter(
            'roadiz_core.documents_lib_dir',
            $projectDir.DIRECTORY_SEPARATOR.trim($config['documentsLibDir'], "/ \t\n\r\0\x0B")
        );
        /*
         * Media config
         */
        $container->setParameter(
            'roadiz_core.medias.ffmpeg_path',
            $config['medias']['ffmpeg_path'] ?? null
        );
        $container->setParameter(
            'roadiz_core.medias.unsplash_client_id',
            $config['medias']['unsplash_client_id'] ?? ''
        );
        $container->setParameter(
            'roadiz_core.medias.google_server_id',
            $config['medias']['google_server_id'] ?? null
        );
        $container->setParameter(
            'roadiz_core.medias.soundcloud_client_id',
            $config['medias']['soundcloud_client_id'] ?? null
        );
        $container->setParameter('roadiz_core.medias.supported_platforms', []);

        $container->setParameter('roadiz_core.webhook.message_types', [
            'webhook.type.generic_json_post' => GenericJsonPostMessageInterface::class,
            'webhook.type.gitlab_pipeline' => GitlabPipelineTriggerMessageInterface::class,
            'webhook.type.netlify_build_hook' => NetlifyBuildHookMessageInterface::class,
        ]);

        $this->registerEntityGenerator($config, $container);
        $this->registerReverseProxyCache($config, $container);
        $this->registerSolr($config, $container);
        $this->registerMarkdown($config, $container);
        $this->registerCaptcha($config, $container);
    }

    private function registerCaptcha(array $config, ContainerBuilder $container): void
    {
        $verifyUrl = $config['captcha']['verify_url'] ?? $config['medias']['recaptcha_verify_url'] ?? null;
        $container->setParameter(
            'roadiz_core.captcha.private_key',
            $config['captcha']['private_key'] ?? $config['medias']['recaptcha_private_key'] ?? null
        );
        $container->setParameter(
            'roadiz_core.captcha.public_key',
            $config['captcha']['public_key'] ?? $config['medias']['recaptcha_public_key'] ?? null
        );
        $container->setParameter(
            'roadiz_core.captcha.verify_url',
            $verifyUrl
        );
    }

    private function registerReverseProxyCache(array $config, ContainerBuilder $container): void
    {
        $reverseProxyCacheFrontendsReferences = [];
        if (isset($config['reverseProxyCache'])) {
            foreach ($config['reverseProxyCache']['frontend'] as $name => $frontend) {
                $definitionName = 'roadiz_core.reverse_proxy_cache.frontends.'.$name;
                $container->setDefinition(
                    $definitionName,
                    (new Definition())
                        ->setClass(ReverseProxyCache::class)
                        ->setPublic(true)
                        ->setArguments([
                            $name,
                            $frontend['host'],
                            $frontend['domainName'],
                            $frontend['timeout'],
                        ])
                );
                $reverseProxyCacheFrontendsReferences[] = new Reference($definitionName);
            }

            if (isset($config['reverseProxyCache']['cloudflare'])) {
                $container->setDefinition(
                    'roadiz_core.reverse_proxy_cache.cloudflare',
                    (new Definition())
                        ->setClass(CloudflareProxyCache::class)
                        ->setPublic(true)
                        ->setArguments([
                            'cloudflare',
                            $config['reverseProxyCache']['cloudflare']['zone'],
                            $config['reverseProxyCache']['cloudflare']['version'],
                            $config['reverseProxyCache']['cloudflare']['bearer'] ?? null,
                            $config['reverseProxyCache']['cloudflare']['email'] ?? null,
                            $config['reverseProxyCache']['cloudflare']['key'] ?? null,
                            $config['reverseProxyCache']['cloudflare']['timeout'],
                        ])
                );
            }
        }

        $container->setDefinition(
            ReverseProxyCacheLocator::class,
            (new Definition())
                ->setClass(ReverseProxyCacheLocator::class)
                ->setPublic(true)
                ->setArguments([
                    $reverseProxyCacheFrontendsReferences,
                    new Reference(
                        'roadiz_core.reverse_proxy_cache.cloudflare',
                        ContainerInterface::NULL_ON_INVALID_REFERENCE
                    ),
                ])
        );
    }

    private function registerEntityGenerator(array $config, ContainerBuilder $container): void
    {
        $entityGeneratorFactoryOptions = [
            'parent_class' => NodesSources::class,
            'repository_class' => NodesSourcesRepository::class,
            'node_class' => Node::class,
            'document_class' => Document::class,
            'document_proxy_class' => NodesSourcesDocuments::class,
            'custom_form_class' => CustomForm::class,
            'custom_form_proxy_class' => NodesCustomForms::class,
            'translation_class' => Translation::class,
            'namespace' => NodeType::getGeneratedEntitiesNamespace(),
            'use_native_json' => $config['useNativeJsonColumnType'],
            'use_api_platform_filters' => true,
        ];
        $container->setParameter('roadiz_core.entity_generator_factory.options', $entityGeneratorFactoryOptions);
    }

    private function registerSolr(array $config, ContainerBuilder $container): void
    {
        if (!isset($config['solr'])) {
            return;
        }
        $solrEndpoints = [];
        $container->setDefinition(
            'roadiz_core.solr.adapter',
            (new Definition())
                ->setClass(Curl::class)
                ->setPublic(true)
                ->addMethodCall('setTimeout', [$config['solr']['timeout']])
                ->addMethodCall('setConnectionTimeout', [$config['solr']['timeout']])
        );
        foreach ($config['solr']['endpoints'] as $name => $endpoint) {
            $container->setDefinition(
                'roadiz_core.solr.endpoints.'.$name,
                (new Definition())
                    ->setClass(Endpoint::class)
                    ->setPublic(true)
                    ->setArguments([
                        $endpoint,
                    ])
                    ->addMethodCall('setKey', [$name])
            );
            $solrEndpoints[] = 'roadiz_core.solr.endpoints.'.$name;
        }
        if (count($solrEndpoints) > 0) {
            $logger = new Reference(SolariumLogger::class);
            $container->setDefinition(
                'roadiz_core.solr.client',
                (new Definition())
                    ->setClass(Client::class)
                    ->setLazy(true)
                    ->setPublic(true)
                    ->setShared(true)
                    ->setArguments([
                        new Reference('roadiz_core.solr.adapter'),
                        new Reference(EventDispatcherInterface::class),
                    ])
                    ->addMethodCall('registerPlugin', ['roadiz_core.solr.client.logger', $logger])
                    ->addMethodCall('setEndpoints', [array_map(function (string $endpointId) {
                        return new Reference($endpointId);
                    }, $solrEndpoints)])
            );
        }
        $container->setParameter('roadiz_core.solr.clients', $solrEndpoints);
    }

    private function registerMarkdown(array $config, ContainerBuilder $container): void
    {
        $container->setParameter('roadiz_core.markdown_config_default', [
            'external_link' => [
                'open_in_new_window' => true,
                'noopener' => 'external',
                'noreferrer' => 'external',
            ],
        ]);
        /** @var array $defaultConfig */
        $defaultConfig = $container->getParameter('roadiz_core.markdown_config_default');
        $container->setParameter(
            'roadiz_core.markdown_config_text_converter',
            array_merge($defaultConfig, [
                'html_input' => 'allow',
            ])
        );
        $container->setParameter(
            'roadiz_core.markdown_config_text_extra_converter',
            array_merge($defaultConfig, [
                'html_input' => 'allow',
            ])
        );
        $container->setParameter(
            'roadiz_core.markdown_config_line_converter',
            array_merge($defaultConfig, [
                'html_input' => 'escape',
            ])
        );

        $container->setDefinition(
            'roadiz_core.markdown.environments.text_converter',
            (new Definition())
                ->setClass(Environment::class)
                ->setShared(true)
                ->setPublic(true)
                ->setArguments([
                    '%roadiz_core.markdown_config_text_converter%',
                ])
        );

        $container->setDefinition(
            'roadiz_core.markdown.converters.text_converter',
            (new Definition())
                ->setClass(MarkdownConverter::class)
                ->setShared(true)
                ->setPublic(true)
                ->setArguments([
                    new Reference('roadiz_core.markdown.environments.text_converter'),
                ])
        );

        $container->setDefinition(
            'roadiz_core.markdown.environments.text_extra_converter',
            (new Definition())
                ->setClass(Environment::class)
                ->setShared(true)
                ->setPublic(true)
                ->setArguments([
                    '%roadiz_core.markdown_config_text_extra_converter%',
                ])
        );

        $container->setDefinition(
            'roadiz_core.markdown.converters.text_extra_converter',
            (new Definition())
                ->setClass(MarkdownConverter::class)
                ->setShared(true)
                ->setPublic(true)
                ->setArguments([
                    new Reference('roadiz_core.markdown.environments.text_extra_converter'),
                ])
        );

        $container->setDefinition(
            'roadiz_core.markdown.environments.line_converter',
            (new Definition())
                ->setClass(Environment::class)
                ->setShared(true)
                ->setPublic(true)
                ->setArguments([
                    '%roadiz_core.markdown_config_line_converter%',
                ])
        );

        $container->setDefinition(
            'roadiz_core.markdown.converters.line_converter',
            (new Definition())
                ->setClass(MarkdownConverter::class)
                ->setShared(true)
                ->setPublic(true)
                ->setArguments([
                    new Reference('roadiz_core.markdown.environments.line_converter'),
                ])
        );

        $container->setDefinition(
            MarkdownInterface::class,
            (new Definition())
                ->setClass(CommonMark::class)
                ->setShared(true)
                ->setArguments([
                    new Reference('roadiz_core.markdown.converters.text_converter'),
                    new Reference('roadiz_core.markdown.converters.text_extra_converter'),
                    new Reference('roadiz_core.markdown.converters.line_converter'),
                    new Reference(Stopwatch::class),
                ])
        );
    }
}
