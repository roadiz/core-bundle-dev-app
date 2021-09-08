<?php

namespace App;

use RZ\Roadiz\Core\Models\FileAwareInterface;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel implements FileAwareInterface
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/' . $this->environment . '/*.yaml');

        if (is_file(\dirname(__DIR__) . '/config/services.yaml')) {
            $container->import('../config/services.yaml');
            $container->import('../config/{services}_' . $this->environment . '.yaml');
        } else {
            $container->import('../config/{services}.php');
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/' . $this->environment . '/*.yaml');
        $routes->import('../config/{routes}/*.yaml');

        if (is_file(\dirname(__DIR__) . '/config/routes.yaml')) {
            $routes->import('../config/routes.yaml');
        } else {
            $routes->import('../config/{routes}.php');
        }
    }

    public function getPublicFilesPath(): string
    {
        return $this->getProjectDir() . '/public' . $this->getPublicFilesBasePath();
    }

    public function getPublicFilesBasePath(): string
    {
        return '/files';
    }

    public function getPrivateFilesPath(): string
    {
        $this->getProjectDir() . '/var' . $this->getPrivateFilesBasePath();
    }

    public function getPrivateFilesBasePath(): string
    {
        return '/files/private';
    }

    public function getFontsFilesPath(): string
    {
        $this->getProjectDir() . '/var' . $this->getFontsFilesBasePath();
    }

    public function getFontsFilesBasePath(): string
    {
        return '/files/fonts';
    }

    public function getPublicCachePath(): string
    {
        $this->getProjectDir() . '/public' . $this->getPublicCacheBasePath();
    }

    public function getPublicCacheBasePath(): string
    {
        return '/assets';
    }


}
