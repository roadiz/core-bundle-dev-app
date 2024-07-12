<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\DependencyInjection\Configuration;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;

#[AsDoctrineListener(event: Events::postLoad)]
#[AsDoctrineListener(event: Events::loadClassMetadata)]
final class NodesSourcesInheritanceSubscriber
{
    public function __construct(
        private readonly NodeTypes $nodeTypes,
        private readonly string $inheritanceType
    ) {
    }

    public function postLoad(PostLoadEventArgs $event): void
    {
        $object = $event->getObject();
        if (!$object instanceof NodesSources) {
            return;
        }
        $object->injectObjectManager($event->getObjectManager());
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        // the $metadata is all the mapping info for this class
        $metadata = $eventArgs->getClassMetadata();
        // the annotation reader accepts a ReflectionClass, which can be
        // obtained from the $metadata
        $class = $metadata->getReflectionClass();

        if ($class->getName() === NodesSources::class) {
            try {
                /** @var NodeType[] $nodeTypes */
                $nodeTypes = $this->nodeTypes->all();
                $map = [];
                foreach ($nodeTypes as $type) {
                    $map[\mb_strtolower($type->getName())] = $type->getSourceEntityFullQualifiedClassName();
                }
                $metadata->setDiscriminatorMap($map);

                /*
                 * MAKE SURE these parameters are synced with NodesSources.php annotations.
                 */
                $nodeSourceTableAnnotation = [
                    'name' => $metadata->getTableName(),
                    'indexes' => [
                        ['columns' => ['discr']],
                        ['columns' => ['title']],
                        ['columns' => ['published_at']],
                        'ns_node_translation_published' => ['columns' => ['node_id', 'translation_id', 'published_at']],
                        'ns_node_discr_translation' => ['columns' => ['node_id', 'discr', 'translation_id']],
                        'ns_node_discr_translation_published' => ['columns' => ['node_id', 'discr', 'translation_id', 'published_at']],
                        'ns_translation_published' => ['columns' => ['translation_id', 'published_at']],
                        'ns_discr_translation' => ['columns' => ['discr', 'translation_id']],
                        'ns_discr_translation_published' => ['columns' => ['discr', 'translation_id', 'published_at']],
                        'ns_title_published' => ['columns' => ['title', 'published_at']],
                        'ns_title_translation_published' => ['columns' => ['title', 'translation_id', 'published_at']],
                    ],
                    'uniqueConstraints' => [
                        ['columns' => ["node_id", "translation_id"]]
                    ]
                ];

                if ($this->inheritanceType === Configuration::INHERITANCE_TYPE_JOINED) {
                    $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_JOINED);
                } elseif ($this->inheritanceType === Configuration::INHERITANCE_TYPE_SINGLE_TABLE) {
                    $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_SINGLE_TABLE);
                    /*
                     * If inheritance type is single table, we need to set indexes on parent class: NodesSources
                     */
                    foreach ($nodeTypes as $type) {
                        $indexedFields = $type->getFields()->filter(function (NodeTypeFieldInterface $field) {
                            return $field->isIndexed();
                        });
                        /** @var NodeTypeFieldInterface $indexedField */
                        foreach ($indexedFields as $indexedField) {
                            $nodeSourceTableAnnotation['indexes']['nsapp_' . $indexedField->getName()] = [
                                'columns' => [$indexedField->getName()],
                            ];
                        }
                    }
                } else {
                    throw new \RuntimeException('Inheritance type not supported: ' . $this->inheritanceType);
                }
                $metadata->setPrimaryTable($nodeSourceTableAnnotation);
            } catch (\Exception $e) {
                /*
                 * Database tables don't exist yet
                 * Need Install
                 */
            }
        }
    }
}
