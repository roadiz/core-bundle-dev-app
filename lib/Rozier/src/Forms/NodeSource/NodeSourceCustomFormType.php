<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms\NodeSource;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeHandler;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @package RZ\Roadiz\CMS\Forms\NodeSource
 */
final class NodeSourceCustomFormType extends AbstractNodeSourceFieldType
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
            'class' => CustomForm::class,
            'multiple' => true,
            'property' => 'id',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'custom_forms';
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

        $event->setData($this->managerRegistry
            ->getRepository(CustomForm::class)
            ->findByNodeAndField($nodeSource->getNode(), $nodeTypeField));
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
        $this->nodeHandler->cleanCustomFormsFromField($nodeTypeField, false);

        if (is_array($event->getData())) {
            $position = 0.0;
            foreach ($event->getData() as $customFormId) {
                $manager = $this->managerRegistry->getManager();
                /** @var CustomForm|null $tempCForm */
                $tempCForm = $manager->find(CustomForm::class, (int) $customFormId);

                if ($tempCForm !== null) {
                    $this->nodeHandler->addCustomFormForField($tempCForm, $nodeTypeField, false, $position);
                    $position++;
                } else {
                    throw new \RuntimeException('Custom form #' . $customFormId . ' was not found during relationship creation.');
                }
            }
        }
    }
}
