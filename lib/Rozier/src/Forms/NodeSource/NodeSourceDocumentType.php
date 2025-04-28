<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms\NodeSource;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\EntityHandler\NodesSourcesHandler;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NodeSourceDocumentType extends AbstractNodeSourceFieldType
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly NodesSourcesHandler $nodesSourcesHandler,
    ) {
        parent::__construct($managerRegistry);
    }

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

    public function getBlockPrefix(): string
    {
        return 'documents';
    }

    public function onPreSetData(FormEvent $event): void
    {
        /** @var NodesSources $nodeSource */
        $nodeSource = $event->getForm()->getConfig()->getOption('nodeSource');
        /** @var NodeTypeField $nodeTypeField */
        $nodeTypeField = $event->getForm()->getConfig()->getOption('nodeTypeField');

        // TODO: Send imageCropAlignment and hotspot through Form data
        $event->setData(array_map(fn (NodesSourcesDocuments $nsd) => $nsd->getDocument(), $this->managerRegistry
            ->getRepository(NodesSourcesDocuments::class)
            ->findByNodesSourcesAndFieldName(
                $nodeSource,
                $nodeTypeField->getName()
            )));
    }

    /**
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

        if (!is_array($event->getData())) {
            return;
        }

        $position = 0.0;
        $manager = $this->managerRegistry->getManager();
        foreach ($event->getData() as $documentId) {
            /** @var Document|null $tempDoc */
            $tempDoc = $manager->find(Document::class, (int) $documentId);

            if (null !== $tempDoc) {
                // TODO: Send imageCropAlignment and hotspot through Form data
                $hotspot = null;
                $imageCropAlignment = null;
                $this->nodesSourcesHandler->addDocumentForField(
                    $tempDoc,
                    $nodeTypeField,
                    false,
                    $position,
                    $hotspot,
                    $imageCropAlignment
                );
                ++$position;
            } else {
                throw new \RuntimeException('Document #'.$documentId.' was not found during relationship creation.');
            }
        }
    }
}
