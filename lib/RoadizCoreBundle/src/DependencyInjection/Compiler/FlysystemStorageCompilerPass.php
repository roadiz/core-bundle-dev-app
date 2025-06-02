<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection\Compiler;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use League\FlysystemBundle\Adapter\AdapterDefinitionFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class FlysystemStorageCompilerPass implements CompilerPassInterface
{
    protected function getStorageReference(
        ContainerBuilder $container,
        string $storageName,
        array $storageConfig,
        ?string $publicUrl = null,
    ): Reference {
        if (!$container->hasDefinition($storageName)) {
            $definitionFactory = new AdapterDefinitionFactory();
            $adapterName = 'flysystem.adapter.'.$storageName;
            if ($adapter = $definitionFactory->createDefinition('local', $storageConfig['options'])) {
                $container->setDefinition($adapterName, $adapter)->setPublic(false);
                $container->setDefinition(
                    $storageName,
                    $this->createStorageDefinition($storageName, new Reference($adapterName), $publicUrl)
                );
            }
        }

        return new Reference($storageName);
    }

    protected function getDocumentPublicStorage(ContainerBuilder $container): Reference
    {
        return $this->getStorageReference(
            $container,
            'documents_public.storage',
            ['options' => ['directory' => '%kernel.project_dir%/public/files']],
            '/files/'
        );
    }

    protected function getDocumentPrivateStorage(ContainerBuilder $container): Reference
    {
        return $this->getStorageReference(
            $container,
            'documents_private.storage',
            ['options' => ['directory' => '%kernel.project_dir%/var/files/private']]
        );
    }

    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        $container->setDefinition(
            'roadiz_core.flysystem.mount_manager',
            (new Definition())
                ->setClass(MountManager::class)
                ->setArguments([[
                    'public' => $this->getDocumentPublicStorage($container),
                    'private' => $this->getDocumentPrivateStorage($container),
                ]])
                ->addTag('flysystem.storage', ['storage' => 'documents.storage'])
        );

        $container->setAlias('documents.storage', 'roadiz_core.flysystem.mount_manager');
        $container->setAlias(FilesystemOperator::class, 'roadiz_core.flysystem.mount_manager');
    }

    private function createStorageDefinition(string $storageName, Reference $adapter, ?string $publicUrl = null): Definition
    {
        $definition = new Definition(Filesystem::class);
        $definition->setPublic(false);
        $definition->setArgument(0, $adapter);
        $definition->setArgument(1, [
            'public_url' => $publicUrl,
            'visibility' => null,
            'directory_visibility' => null,
            'case_sensitive' => true,
            'disable_asserts' => false,
        ]);
        $definition->addTag('flysystem.storage', ['storage' => $storageName]);

        return $definition;
    }
}
