<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form\NodeSource;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\EntityHandler\NodesSourcesHandler;
use RZ\Roadiz\CoreBundle\Repository\DocumentRepository;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesDocumentsRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NodeSourceDocumentType extends AbstractNodeSourceFieldType
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly NodesSourcesHandler $nodesSourcesHandler,
        private readonly NodesSourcesDocumentsRepository $nodesSourcesDocumentsRepository,
        private readonly DocumentRepository $documentRepository,
    ) {
        parent::__construct($managerRegistry);
    }

    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['_locale'] = $options['_locale'];
        $view->vars['entityName'] = 'node-source-document';
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
            'class' => Document::class,
            'multiple' => true,
            'property' => 'id',
            '_locale' => null,
        ]);

        $resolver->setRequired([
            'label',
        ]);
        $resolver->addAllowedTypes('_locale', ['string', 'null']);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'node_source_documents';
    }

    public function onPreSetData(FormEvent $event): void
    {
        /** @var NodesSources $nodeSource */
        $nodeSource = $event->getForm()->getConfig()->getOption('nodeSource');
        /** @var NodeTypeField $nodeTypeField */
        $nodeTypeField = $event->getForm()->getConfig()->getOption('nodeTypeField');

        $event->setData($this->nodesSourcesDocumentsRepository->findByNodesSourcesAndFieldName(
            $nodeSource,
            $nodeTypeField->getName()
        ));
    }

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
        foreach ($event->getData() as $documentDto) {
            if (!isset($documentDto['document'])) {
                throw new \RuntimeException('Document was not found in submitted data.');
            }
            /** @var Document|null $tempDoc */
            $tempDoc = $this->documentRepository->find((int) $documentDto['document']);

            if (null !== $tempDoc) {
                $hotspot = (isset($documentDto['hotspot'])) ? \json_decode($documentDto['hotspot'], true, flags: JSON_THROW_ON_ERROR) : null;
                $imageCropAlignment = $documentDto['imageCropAlignment'] ?? null;
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
                throw new \RuntimeException('Document #'.$documentDto['document'].' was not found during relationship creation.');
            }
        }
    }
}
