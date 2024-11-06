<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityHandler;

use Psr\Container\ContainerInterface;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\Core\Handlers\AbstractHandler;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\CustomFormField;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\Group;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\Translation;

final class HandlerFactory implements HandlerFactoryInterface
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getHandler(AbstractEntity $entity): AbstractHandler
    {
        switch (true) {
            case $entity instanceof Node:
                return $this->container->get(NodeHandler::class)->setNode($entity);
            case $entity instanceof NodesSources:
                return $this->container->get(NodesSourcesHandler::class)->setNodeSource($entity);
            case $entity instanceof NodeType:
                return $this->container->get(NodeTypeHandler::class)->setNodeType($entity);
            case $entity instanceof NodeTypeField:
                return $this->container->get(NodeTypeFieldHandler::class)->setNodeTypeField($entity);
            case $entity instanceof Document:
                return $this->container->get(DocumentHandler::class)->setDocument($entity);
            case $entity instanceof CustomForm:
                return $this->container->get(CustomFormHandler::class)->setCustomForm($entity);
            case $entity instanceof CustomFormField:
                return $this->container->get(CustomFormFieldHandler::class)->setCustomFormField($entity);
            case $entity instanceof Folder:
                return $this->container->get(FolderHandler::class)->setFolder($entity);
            case $entity instanceof Group:
                return $this->container->get(GroupHandler::class)->setGroup($entity);
            case $entity instanceof Tag:
                return $this->container->get(TagHandler::class)->setTag($entity);
            case $entity instanceof Translation:
                return $this->container->get(TranslationHandler::class)->setTranslation($entity);
        }

        throw new \InvalidArgumentException('HandlerFactory does not support '.get_class($entity));
    }
}
