<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\CacheStorage;

class RateLimitersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('limiter')) {
            throw new LogicException('Rate limiter support cannot be enabled as the RateLimiter component is not installed. Try running "composer require symfony/rate-limiter".');
        }

        // default configuration (when used by other DI extensions)
        $name = 'throttled_webhooks';
        $limiterConfig = [
            'cache_pool' => 'cache.rate_limiter',
            'policy' => 'token_bucket',
            'limit' => 1,
            'rate' => [ 'interval' => '10 seconds'],
        ];
        $limiter = $container->setDefinition(
            $limiterId = 'limiter.' . $name,
            new ChildDefinition('limiter')
        );
        $container->register(
            $storageId = 'limiter.storage.' . $name,
            CacheStorage::class
        )->addArgument(new Reference($limiterConfig['cache_pool']));

        $limiter->replaceArgument(1, new Reference($storageId));
        unset($limiterConfig['cache_pool']);
        $limiterConfig['id'] = $name;
        $limiter->replaceArgument(0, $limiterConfig);
        $container->registerAliasForArgument($limiterId, RateLimiterFactory::class, $name . '.limiter');
    }
}
