<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator;

use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\UnicodeString;

final class RepositoryGenerator implements RepositoryGeneratorInterface
{
    private array $options;

    public function __construct(private readonly NodeTypeInterface $nodeType, array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    public function getClassContent(): string
    {
        $file = new PhpFile();
        $file->setStrictTypes();
        $file->addComment('THIS IS A GENERATED FILE, DO NOT EDIT IT.');
        $file->addComment('IT WILL BE RECREATED AT EACH NODE-TYPE UPDATE.');


        $fqcn = $this->options['entity_namespace'] . '\\' . $this->nodeType->getSourceEntityClassName();
        $namespace = $file
            ->addNamespace(trim($this->options['namespace'], '\\'))
            ->addUse('\Doctrine\Persistence\ManagerRegistry')
            ->addUse('\RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface')
            ->addUse('\Symfony\Contracts\EventDispatcher\EventDispatcherInterface')
            ->addUse('\Symfony\Bundle\SecurityBundle\Security')
            ->addUse('\RZ\Roadiz\CoreBundle\SearchEngine\NodeSourceSearchHandlerInterface')
            ->addUse($this->options['parent_class'])
            ->addUse($fqcn)
        ;

        $class = $namespace
            ->addClass($this->options['class_name'])
            ->setFinal()
            ->setExtends($this->options['parent_class'])
        ;

        $simplifiedFqcn = $namespace->simplifyName($fqcn);
        $class
            ->addComment('@extends ' . $namespace->simplifyName($this->options['parent_class']) . '<' . $simplifiedFqcn . '>')
            ->addComment('@method ' . $simplifiedFqcn . '|null find($id, $lockMode = null, $lockVersion = null)')
            ->addComment('@method ' . $simplifiedFqcn . '|null findOneBy(array $criteria, array $orderBy = null)')
            ->addComment('@method ' . $simplifiedFqcn . '[]    findAll()')
            ->addComment('@method ' . $simplifiedFqcn . '[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)')
        ;

        $constructor = $class->addMethod('__construct')
            ->setBody(
                'parent::__construct($registry, $previewResolver, $dispatcher, $security, $nodeSourceSearchHandler, ' . $simplifiedFqcn . '::class);'
            );

        $constructor->addParameter('registry')
            ->setType('\Doctrine\Persistence\ManagerRegistry');
        $constructor->addParameter('previewResolver')
            ->setType('\RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface');
        $constructor->addParameter('dispatcher')
            ->setType('\Symfony\Contracts\EventDispatcher\EventDispatcherInterface');
        $constructor->addParameter('security')
            ->setType('\Symfony\Bundle\SecurityBundle\Security');
        $constructor->addParameter('nodeSourceSearchHandler')
            ->setType('?\RZ\Roadiz\CoreBundle\SearchEngine\NodeSourceSearchHandlerInterface');

        return (new PsrPrinter())->printFile($file);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'parent_class',
            'entity_namespace',
            'namespace',
            'class_name',
        ]);
        $resolver->setAllowedTypes('parent_class', 'string');
        $resolver->setAllowedTypes('entity_namespace', 'string');
        $resolver->setAllowedTypes('namespace', 'string');
        $resolver->setAllowedTypes('class_name', 'string');

        $normalizeClassName = function (OptionsResolver $resolver, string $className) {
            return (new UnicodeString($className))->startsWith('\\') ?
                $className :
                '\\' . $className;
        };

        $resolver->setNormalizer('parent_class', $normalizeClassName);
        $resolver->setNormalizer('entity_namespace', $normalizeClassName);
        $resolver->setNormalizer('namespace', $normalizeClassName);
    }
}
