<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\CoreBundle\Form\DataTransformer\AttributeDocumentsTransformer;
use RZ\Roadiz\CoreBundle\Model\AttributeInterface;
use RZ\Roadiz\CoreBundle\Entity\Attribute;
use RZ\Roadiz\CoreBundle\Entity\AttributeDocuments;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttributeDocumentType extends AbstractType
{
    protected EntityManagerInterface $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            [$this, 'onPostSubmit']
        );
        $builder->addModelTransformer(new AttributeDocumentsTransformer(
            $this->entityManager,
            $options['attribute']
        ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'class' => AttributeDocuments::class,
        ]);

        $resolver->setRequired('attribute');
        $resolver->setAllowedTypes('attribute', [AttributeInterface::class]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'documents';
    }

    /**
     * @inheritDoc
     */
    public function getParent(): ?string
    {
        return CollectionType::class;
    }

    /**
     * Delete existing document association.
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event): void
    {
        if ($event->getForm()->getConfig()->getOption('attribute') instanceof AttributeInterface) {
            /** @var AttributeInterface $attribute */
            $attribute = $event->getForm()->getConfig()->getOption('attribute');

            if ($attribute instanceof Attribute && $attribute->getId()) {
                $qb = $this->entityManager->getRepository(AttributeDocuments::class)
                    ->createQueryBuilder('ad');
                $qb->delete()
                    ->andWhere($qb->expr()->eq('ad.attribute', ':attribute'))
                    ->setParameter(':attribute', $attribute);
                $qb->getQuery()->execute();
            }
        }
    }
}
