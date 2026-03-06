<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RoadizSolrExtension extends Extension
{
    #[\Override]
    public function getAlias(): string
    {
        return 'roadiz_solr';
    }

    #[\Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__).'/../config'));
        $loader->load('services.yaml');

        $fuzzySearchConfig = $this->resolveFuzzySearchConfig($configs, $config, $container);
        $container->setParameter('roadiz_solr.search.fuzzy_proximity', $fuzzySearchConfig['fuzzy_proximity']);
        $container->setParameter('roadiz_solr.search.fuzzy_min_term_length', $fuzzySearchConfig['fuzzy_min_term_length']);
    }

    /**
     * @return array{fuzzy_proximity: mixed, fuzzy_min_term_length: mixed}
     */
    private function resolveFuzzySearchConfig(array $configs, array $config, ContainerBuilder $container): array
    {
        $hasRoadizSolrFuzzyProximity = false;
        $hasRoadizSolrFuzzyMinTermLength = false;

        foreach ($configs as $singleConfig) {
            if (isset($singleConfig['search']['fuzzy_proximity'])) {
                $hasRoadizSolrFuzzyProximity = true;
            }
            if (isset($singleConfig['search']['fuzzy_min_term_length'])) {
                $hasRoadizSolrFuzzyMinTermLength = true;
            }
        }

        $fuzzyProximity = $hasRoadizSolrFuzzyProximity ? $config['search']['fuzzy_proximity'] : null;
        $fuzzyMinTermLength = $hasRoadizSolrFuzzyMinTermLength ? $config['search']['fuzzy_min_term_length'] : null;

        foreach ($container->getExtensionConfig('roadiz_core') as $legacyConfig) {
            if (null === $fuzzyProximity && isset($legacyConfig['solr']['search']['fuzzy_proximity'])) {
                $fuzzyProximity = $legacyConfig['solr']['search']['fuzzy_proximity'];
            }
            if (null === $fuzzyMinTermLength && isset($legacyConfig['solr']['search']['fuzzy_min_term_length'])) {
                $fuzzyMinTermLength = $legacyConfig['solr']['search']['fuzzy_min_term_length'];
            }
        }

        return [
            'fuzzy_proximity' => $fuzzyProximity ?? $config['search']['fuzzy_proximity'],
            'fuzzy_min_term_length' => $fuzzyMinTermLength ?? $config['search']['fuzzy_min_term_length'],
        ];
    }
}
