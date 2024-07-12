<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms\NodeSource;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\EntityHandler\NodesSourcesHandler;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @package RZ\Roadiz\CMS\Forms\NodeSource
 */
final class NodeSourceDocumentType extends AbstractNodeSourceFieldType
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly NodesSourcesHandler $nodesSourcesHandler
    ) {
        parent::__construct($managerRegistry);
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
            'class' => Document::class,
            'multiple' => true,
            'property' => 'id',
        ]);

        $resolver->setRequired([
            'label',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'documents';
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
            ->getRepository(Document::class)
            ->findByNodeSourceAndFieldName(
                $nodeSource,
                $nodeTypeField->getName()
            ));
    }

    /**
     * @param FormEvent $event
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function onPostSubmit(FormEvent $event): void
    {
        /** @var NodesSources $nodeSource */
        $nodeSource = $event->getForm()->getConfig()->getOption('nodeSource');
        /** @var NodeTypeField $nodeTypeField */
        $nodeTypeField = $event->getForm()->getConfig()->getOption('nodeTypeField');

        $this->nodesSourcesHandler->setNodeSource($nodeSource);
        $this->nodesSourcesHandler->cleanDocumentsFromField($nodeTypeField, false);

        if (is_array($event->getData())) {
            $position = 0.0;
            $manager = $this->managerRegistry->getManager();
            foreach ($event->getData() as $documentId) {
                /** @var Document|null $tempDoc */
                $tempDoc = $manager->find(Document::class, (int) $documentId);

                if ($tempDoc !== null) {
                    $this->nodesSourcesHandler->addDocumentForField($tempDoc, $nodeTypeField, false, $position);
                    $position++;
                } else {
                    throw new \RuntimeException('Document #' . $documentId . ' was not found during relationship creation.');
                }
            }
        }
    }
}
