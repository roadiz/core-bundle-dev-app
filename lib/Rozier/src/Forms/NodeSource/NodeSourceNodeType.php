<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms\NodeSource;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeHandler;
use RZ\Roadiz\CoreBundle\Repository\NodeRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @package RZ\Roadiz\CMS\Forms\NodeSource
 */
final class NodeSourceNodeType extends AbstractNodeSourceFieldType
{
    protected NodeHandler $nodeHandler;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param NodeHandler $nodeHandler
     */
    public function __construct(ManagerRegistry $managerRegistry, NodeHandler $nodeHandler)
    {
        parent::__construct($managerRegistry);
        $this->nodeHandler = $nodeHandler;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            [$this, 'onPreSetData']
        )
            ->addEventListener(
                FormEvents::POST_SUBMIT,
                [$this, 'onPostSubmit']
            )
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'required' => false,
            'mapped' => false,
            'class' => Node::class,
            'multiple' => true,
            'property' => 'id',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'nodes';
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event): void
    {
        /** @var NodesSources $nodeSource */
        $nodeSource = $event->getForm()->getConfig()->getOption('nodeSource');

        /** @var NodeTypeField $nodeTypeField */
        $nodeTypeField = $event->getForm()->getConfig()->getOption('nodeTypeField');

        /** @var NodeRepository $nodeRepo */
        $nodeRepo = $this->managerRegistry
            ->getRepository(Node::class)
            ->setDisplayingNotPublishedNodes(true);
        $event->setData($nodeRepo->findByNodeAndField(
            $nodeSource->getNode(),
            $nodeTypeField
        ));
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event): void
    {
        /** @var NodesSources $nodeSource */
        $nodeSource = $event->getForm()->getConfig()->getOption('nodeSource');

        /** @var NodeTypeField $nodeTypeField */
        $nodeTypeField = $event->getForm()->getConfig()->getOption('nodeTypeField');

        $this->nodeHandler->setNode($nodeSource->getNode());
        $this->nodeHandler->cleanNodesFromField($nodeTypeField, false);

        if (is_array($event->getData())) {
            $position = 0.0;
            $manager = $this->managerRegistry->getManager();
            foreach ($event->getData() as $nodeId) {
                /** @var Node|null $tempNode */
                $tempNode = $manager->find(Node::class, (int) $nodeId);

                if ($tempNode !== null) {
                    $this->nodeHandler->addNodeForField($tempNode, $nodeTypeField, false, $position);
                    $position++;
                } else {
                    throw new \RuntimeException('Node #' . $nodeId . ' was not found during relationship creation.');
                }
            }
        }
    }
}
