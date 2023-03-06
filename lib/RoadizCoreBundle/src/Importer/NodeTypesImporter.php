<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Importer;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeTypeHandler;
use RZ\Roadiz\CoreBundle\Message\UpdateNodeTypeSchemaMessage;
use RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor\TypedObjectConstructorInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class NodeTypesImporter implements EntityImporterInterface
{
    protected SerializerInterface $serializer;
    protected HandlerFactoryInterface $handlerFactory;


    public function __construct(SerializerInterface $serializer, HandlerFactoryInterface $handlerFactory)
    {
        $this->serializer = $serializer;
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @inheritDoc
     */
    public function supports(string $entityClass): bool
    {
        return $entityClass === NodeType::class;
    }

    /**
     * @inheritDoc
     */
    public function import(string $serializedData): bool
    {
        $nodeType = $this->serializer->deserialize(
            $serializedData,
            NodeType::class,
            'json',
            DeserializationContext::create()
                ->setAttribute(TypedObjectConstructorInterface::PERSIST_NEW_OBJECTS, true)
                ->setAttribute(TypedObjectConstructorInterface::FLUSH_NEW_OBJECTS, true)
        );

        /** @var NodeTypeHandler $nodeTypeHandler */
        $nodeTypeHandler = $this->handlerFactory->getHandler($nodeType);
        $nodeTypeHandler->updateSchema();

        return true;
    }
}
