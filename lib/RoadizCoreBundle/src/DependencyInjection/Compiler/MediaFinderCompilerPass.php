<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MediaFinderCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasParameter('roadiz_core.medias.supported_platforms')) {
            $parameter = $container->getParameter('roadiz_core.medias.supported_platforms');
            $taggedServices = $container->findTaggedServiceIds(
                'roadiz_core.media_finder'
            );
            foreach ($taggedServices as $id => $tags) {
                foreach ($tags as $tag) {
                    if (isset($tag['platform'])) {
                        $parameter[$tag['platform']] = $id;
                    }
                }
            }
            ksort($parameter);
            $container->setParameter('roadiz_core.medias.supported_platforms', $parameter);
        }
    }
}
