<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Attribute;
use RZ\Roadiz\CoreBundle\Entity\AttributeDocuments;
use RZ\Roadiz\CoreBundle\Form\DataTransformer\AttributeDocumentsTransformer;
use RZ\Roadiz\CoreBundle\Model\AttributeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AttributeDocumentType extends AbstractType
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            $this->onPostSubmit(...)
        );
        $builder->addModelTransformer(new AttributeDocumentsTransformer(
            $this->managerRegistry,
            $options['attribute']
        ));
    }

    #[\Override]
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

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'documents';
    }

    #[\Override]
    public function getParent(): ?string
    {
        return CollectionType::class;
    }

    /**
     * Delete existing document association.
     */
    public function onPostSubmit(FormEvent $event): void
    {
        if ($event->getForm()->getConfig()->getOption('attribute') instanceof AttributeInterface) {
            /** @var AttributeInterface $attribute */
            $attribute = $event->getForm()->getConfig()->getOption('attribute');

            if ($attribute instanceof Attribute && $attribute->getId()) {
                $qb = $this->managerRegistry->getRepository(AttributeDocuments::class)
                    ->createQueryBuilder('ad');
                $qb->delete()
                    ->andWhere($qb->expr()->eq('ad.attribute', ':attribute'))
                    ->setParameter(':attribute', $attribute);
                $qb->getQuery()->execute();
            }
        }
    }
}
