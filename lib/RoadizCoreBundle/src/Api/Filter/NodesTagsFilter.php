<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesTags;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

final class NodesTagsFilter extends AbstractFilter
{
    public const PROPERTY_PARAMETER = 'nodesTags';
    public const TRUE_VALUES = [true, '1', 1, 'true', 'on'];
    public const FALSE_VALUES = [false, '0', 0, 'false', 'off'];

    private const DEFAULTS = [
        'withNodes' => false,
        'withoutNodes' => false,
        'visible' => null,
        'nodeName' => [],
        'nodeTypeName' => [],
        'tagName' => [],
        'parentNodeName' => [],
    ];

    public function __construct(
        private readonly PreviewResolverInterface $previewResolver,
        ManagerRegistry $managerRegistry,
        ?LoggerInterface $logger = null,
        ?array $properties = null,
        ?NameConverterInterface $nameConverter = null,
    ) {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter);
    }

    protected function filterProperty(
        string $property,
        mixed $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void
    {
        if (Tag::class !== $resourceClass) {
            return;
        }

        if (self::PROPERTY_PARAMETER !== $property) {
            return;
        }

        if (
            !is_array($value) &&
            (in_array($value, self::TRUE_VALUES, true) || in_array($value, self::FALSE_VALUES, true))
        ) {
            $withNodes = in_array($value, self::TRUE_VALUES, true);
            $withoutNodes = in_array($value, self::FALSE_VALUES, true);

            $this->alterQueryBuilder($queryBuilder, [
                'withNodes' => $withNodes,
                'withoutNodes' => $withoutNodes,
            ]);

            return;
        }

        if (!is_array($value)) {
            return;
        }

        $this->alterQueryBuilder($queryBuilder, $this->extractProperties($value));
    }

    private function extractProperties(array $value): array
    {
        $parameters = [
            ...self::DEFAULTS
        ];

        if (array_key_exists('visible', $value)) {
            $parameters['visible'] = in_array($value['visible'], self::TRUE_VALUES, true);
        }

        if (array_key_exists('nodeName', $value)) {
            $parameters['nodeName'] = is_array($value['nodeName']) ? $value['nodeName'] : [$value['nodeName']];
            $parameters['nodeName'] = array_filter($parameters['nodeName']);
        }

        if (array_key_exists('parentNodeName', $value)) {
            $parameters['parentNodeName'] =  array_filter(is_array($value['parentNodeName']) ? $value['parentNodeName'] : [$value['parentNodeName']]);
        }

        if (array_key_exists('nodeTypeName', $value)) {
            $parameters['nodeTypeName'] = array_filter(is_array($value['nodeTypeName']) ? $value['nodeTypeName'] : [$value['nodeTypeName']]);
        }

        if (array_key_exists('tagName', $value)) {
            $parameters['tagName'] = array_filter(is_array($value['tagName']) ? $value['tagName'] : [$value['tagName']]);
        }

        return array_filter($parameters, fn ($value) => !is_array($value) || count($value) > 0);
    }


    private function alterQueryBuilder(QueryBuilder $queryBuilder, array $parameters): void
    {
        $ntgQb = $this->managerRegistry
            ->getRepository(NodesTags::class)
            ->createQueryBuilder('ntg');
        $ntgQb
            ->select('DISTINCT(IDENTITY(ntg.tag))')
            ->innerJoin('ntg.node', 'n')
        ;

        if ($this->previewResolver->isPreview()) {
            $ntgQb->andWhere($ntgQb->expr()->lte('n.status', ':status'));
            $queryBuilder->setParameter(':status', Node::PUBLISHED);
        } else {
            $ntgQb
                ->innerJoin('n.nodeSources', 'ns')
                ->andWhere($ntgQb->expr()->lte('ns.publishedAt', ':lte_published_at'))
                ->andWhere($ntgQb->expr()->eq('n.status', ':status'));
            $queryBuilder
                ->setParameter(':lte_published_at', new \DateTime())
                ->setParameter(':status', Node::PUBLISHED);
        }

        if (true === $parameters['withoutNodes']) {
            $queryBuilder->andWhere($queryBuilder->expr()->notIn(
                'o.id',
                $ntgQb->getQuery()->getDQL()
            ));
            return;
        }

        if (array_key_exists('visible', $parameters) && is_bool($parameters['visible'])) {
            $ntgQb->andWhere($ntgQb->expr()->eq('n.visible', ':visible'));
            $queryBuilder->setParameter(':visible', $parameters['visible']);
        }

        if (array_key_exists('nodeName', $parameters) && is_array($parameters['nodeName'])) {
            $ntgQb->andWhere($ntgQb->expr()->in('n.nodeName', ':nodeName'));
            $queryBuilder->setParameter(':nodeName', $parameters['nodeName']);
        }

        if (array_key_exists('parentNodeName', $parameters) && is_array($parameters['parentNodeName'])) {
            $ntgQb
                ->innerJoin('n.parentNode', 'pn')
                ->andWhere($ntgQb->expr()->in('pn.nodeName', ':parentNodeName'));
            $queryBuilder->setParameter(':parentNodeName', $parameters['parentNodeName']);
        }

        if (array_key_exists('nodeTypeName', $parameters) && is_array($parameters['nodeTypeName'])) {
            $ntgQb
                ->innerJoin('n.nodeType', 'nt')
                ->andWhere($ntgQb->expr()->in('nt.name', ':nodeTypeName'));
            $queryBuilder->setParameter(':nodeTypeName', $parameters['nodeTypeName']);
        }

        if (array_key_exists('tagName', $parameters) && is_array($parameters['tagName'])) {
            $ntgQb
                ->innerJoin('n.nodesTags', 'ntg_2')
                ->innerJoin('ntg_2.tag', 't_2')
                ->andWhere($ntgQb->expr()->in('t_2.tagName', ':tagName'));
            $queryBuilder->setParameter(':tagName', $parameters['tagName']);
        }

        $queryBuilder->andWhere($queryBuilder->expr()->in(
            'o.id',
            $ntgQb->getDQL()
        ));
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            self::PROPERTY_PARAMETER => [
                'property' => self::PROPERTY_PARAMETER,
                'type' => 'bool',
                'required' => false,
                'description' => 'Filter tags if they are related to any node or not.',
                'openapi' => [
                    'description' => 'Filter tags if they are related to any node or not.',
                ],
            ],
            self::PROPERTY_PARAMETER.'[visible]' => [
                'property' => self::PROPERTY_PARAMETER.'[visible]',
                'type' => 'bool',
                'required' => false,
                'description' => 'Filter tags if they are related to any visible node.',
                'openapi' => [
                    'description' => 'Filter tags if they are related to any visible node.',
                ],
            ],
            self::PROPERTY_PARAMETER.'[nodeTypeName]' => [
                'property' => self::PROPERTY_PARAMETER.'[nodeTypeName]',
                'type' => 'string',
                'required' => false,
                'description' => 'Filter tags if they are related to any node of `nodeTypeName`.',
                'openapi' => [
                    'description' => 'Filter tags if they are related to any node of `nodeTypeName`.',
                ],
            ],
            self::PROPERTY_PARAMETER.'[nodeTypeName][]' => [
                'property' => self::PROPERTY_PARAMETER.'[nodeTypeName][]',
                'type' => 'string',
                'required' => false,
                'description' => 'Filter tags if they are related to any node of `nodeTypeName`.',
                'openapi' => [
                    'description' => 'Filter tags if they are related to any node of `nodeTypeName`.',
                ],
            ],
            self::PROPERTY_PARAMETER.'[tagName]' => [
                'property' => self::PROPERTY_PARAMETER.'[tagName]',
                'type' => 'string',
                'required' => false,
                'description' => 'Filter tags if they are related to any node which is linked to another `tagName`.',
                'openapi' => [
                    'description' => 'Filter tags if they are related to any node which is linked to another `tagName`.',
                ],
            ],
            self::PROPERTY_PARAMETER.'[tagName][]' => [
                'property' => self::PROPERTY_PARAMETER.'[tagName][]',
                'type' => 'string',
                'required' => false,
                'description' => 'Filter tags if they are related to any node which is linked to another `tagName`.',
                'openapi' => [
                    'description' => 'Filter tags if they are related to any node which is linked to another `tagName`.',
                ],
            ],
            self::PROPERTY_PARAMETER.'[nodeName]' => [
                'property' => self::PROPERTY_PARAMETER.'[nodeName]',
                'type' => 'string',
                'required' => false,
                'description' => 'Filter tags if they are related to a node with `nodeName`.',
                'openapi' => [
                    'description' => 'Filter tags if they are related to a node with `nodeName`.',
                ],
            ],
            self::PROPERTY_PARAMETER.'[nodeName][]' => [
                'property' => self::PROPERTY_PARAMETER.'[nodeName][]',
                'type' => 'string',
                'required' => false,
                'description' => 'Filter tags if they are related to a node with `nodeName`.',
                'openapi' => [
                    'description' => 'Filter tags if they are related to a node with `nodeName`.',
                ],
            ],
            self::PROPERTY_PARAMETER.'[parentNodeName]' => [
                'property' => self::PROPERTY_PARAMETER.'[parentNodeName]',
                'type' => 'string',
                'required' => false,
                'description' => 'Filter tags if they are related to a node whom parent-node is `parentNodeName`.',
                'openapi' => [
                    'description' => 'Filter tags if they are related to a node whom parent-node is `parentNodeName`.',
                ],
            ],
            self::PROPERTY_PARAMETER.'[parentNodeName][]' => [
                'property' => self::PROPERTY_PARAMETER.'[parentNodeName][]',
                'type' => 'string',
                'required' => false,
                'description' => 'Filter tags if they are related to a node whom parent-node is `parentNodeName`.',
                'openapi' => [
                    'description' => 'Filter tags if they are related to a node whom parent-node is `parentNodeName`.',
                ],
            ],
        ];
    }
}
