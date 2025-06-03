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
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\Translation;

final readonly class HandlerFactory implements HandlerFactoryInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    #[\Override]
    public function getHandler(AbstractEntity $entity): AbstractHandler
    {
        return match (true) {
            $entity instanceof Node => $this->container->get(NodeHandler::class)->setNode($entity),
            $entity instanceof NodesSources => $this->container->get(NodesSourcesHandler::class)->setNodeSource($entity),
            $entity instanceof Document => $this->container->get(DocumentHandler::class)->setDocument($entity),
            $entity instanceof CustomForm => $this->container->get(CustomFormHandler::class)->setCustomForm($entity),
            $entity instanceof CustomFormField => $this->container->get(CustomFormFieldHandler::class)->setCustomFormField($entity),
            $entity instanceof Folder => $this->container->get(FolderHandler::class)->setFolder($entity),
            $entity instanceof Group => $this->container->get(GroupHandler::class)->setGroup($entity),
            $entity instanceof Tag => $this->container->get(TagHandler::class)->setTag($entity),
            $entity instanceof Translation => $this->container->get(TranslationHandler::class)->setTranslation($entity),
            default => throw new \InvalidArgumentException('HandlerFactory does not support '.$entity::class),
        };
    }
}
