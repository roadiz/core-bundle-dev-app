<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator;

use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\UnicodeString;

class RepositoryGenerator implements RepositoryGeneratorInterface
{
    private NodeTypeInterface $nodeType;
    private array $options;

    public function __construct(NodeTypeInterface $nodeType, array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->nodeType = $nodeType;
        $this->options = $resolver->resolve($options);
    }

    public function getClassContent(): string
    {
        return $this->getClassHeader() . PHP_EOL .
            $this->getClassBody();
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

    /**
     * @return string
     */
    protected function getClassBody(): string
    {
        return 'class ' . $this->options['class_name'] . ' extends ' . $this->options['parent_class'] . '
{' . $this->getClassConstructor() . '}' . PHP_EOL;
    }

    /**
     * @return string
     */
    protected function getClassHeader(): string
    {
        $fqcn = $this->options['entity_namespace'] . '\\' . $this->nodeType->getSourceEntityClassName();
        /*
         * BE CAREFUL, USE statements are required for field generators which
         * are using ::class syntax!
         */
        return '<?php

declare(strict_types=1);

/*
 * THIS IS A GENERATED FILE, DO NOT EDIT IT
 * IT WILL BE RECREATED AT EACH NODE-TYPE UPDATE
 */
namespace ' . ltrim($this->options['namespace'], '\\') . ';

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\NodeSourceSearchHandlerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends ' . $this->options['parent_class'] . '<' . $fqcn . '>
 *
 * @method ' . $fqcn . '|null find($id, $lockMode = null, $lockVersion = null)
 * @method ' . $fqcn . '|null findOneBy(array $criteria, array $orderBy = null)
 * @method ' . $fqcn . '[]    findAll()
 * @method ' . $fqcn . '[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */';
    }

    /**
     * @return string
     */
    protected function getClassConstructor(): string
    {
            return '
    public function __construct(
        ManagerRegistry $registry,
        PreviewResolverInterface $previewResolver,
        EventDispatcherInterface $dispatcher,
        Security $security,
        ?NodeSourceSearchHandlerInterface $nodeSourceSearchHandler
    ) {
        parent::__construct($registry, $previewResolver, $dispatcher, $security, $nodeSourceSearchHandler);

        $this->_entityName = ' . $this->options['entity_namespace'] . '\\' . $this->nodeType->getSourceEntityClassName() . '::class;
    }' . PHP_EOL;
    }
}
