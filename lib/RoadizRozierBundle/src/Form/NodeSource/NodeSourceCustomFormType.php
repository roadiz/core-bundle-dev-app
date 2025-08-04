<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form\NodeSource;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeHandler;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NodeSourceCustomFormType extends AbstractNodeSourceFieldType
{
    public function __construct(
        ManagerRegistry $managerRegistry,
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
            'class' => CustomForm::class,
            'multiple' => true,
            'property' => 'id',
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'custom_forms';
    }

    public function onPreSetData(FormEvent $event): void
    {
        /** @var NodesSources $nodeSource */
        $nodeSource = $event->getForm()->getConfig()->getOption('nodeSource');

        /** @var NodeTypeField $nodeTypeField */
        $nodeTypeField = $event->getForm()->getConfig()->getOption('nodeTypeField');

        $event->setData($this->managerRegistry
            ->getRepository(CustomForm::class)
            ->findByNodeAndFieldName($nodeSource->getNode(), $nodeTypeField->getName()));
    }

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

                if (null !== $tempCForm) {
                    $this->nodeHandler->addCustomFormForField($tempCForm, $nodeTypeField, false, $position);
                    ++$position;
                } else {
                    throw new \RuntimeException('Custom form #'.$customFormId.' was not found during relationship creation.');
                }
            }
        }
    }
}
