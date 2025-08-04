<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form\NodeSource;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeHandler;
use RZ\Roadiz\CoreBundle\Repository\NotPublishedNodeRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NodeSourceNodeType extends AbstractNodeSourceFieldType
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly NotPublishedNodeRepository $notPublishedNodeRepository,
        private readonly NodeHandler $nodeHandler,
    ) {
        parent::__construct($managerRegistry);
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            $this->onPreSetData(...)
        )
            ->addEventListener(
                FormEvents::POST_SUBMIT,
                $this->onPostSubmit(...)
            )
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'required' => false,
            'mapped' => false,
            'class' => Node::class,
            'multiple' => true,
            'property' => 'id',
            '_locale' => null,
        ]);

        $resolver->addAllowedTypes('_locale', ['string', 'null']);
    }

    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['_locale'] = $options['_locale'];
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'nodes';
    }

    public function onPreSetData(FormEvent $event): void
    {
        /** @var NodesSources $nodeSource */
        $nodeSource = $event->getForm()->getConfig()->getOption('nodeSource');

        /** @var NodeTypeField $nodeTypeField */
        $nodeTypeField = $event->getForm()->getConfig()->getOption('nodeTypeField');

        $event->setData($this->notPublishedNodeRepository->findByNodeAndField(
            $nodeSource->getNode(),
            $nodeTypeField
        ));
    }

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

                if (null !== $tempNode) {
                    $this->nodeHandler->addNodeForField($tempNode, $nodeTypeField, false, $position);
                    ++$position;
                } else {
                    throw new \RuntimeException('Node #'.$nodeId.' was not found during relationship creation.');
                }
            }
        }
    }
}
