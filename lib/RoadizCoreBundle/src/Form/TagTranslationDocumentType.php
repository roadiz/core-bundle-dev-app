<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\TagTranslation;
use RZ\Roadiz\CoreBundle\Entity\TagTranslationDocuments;
use RZ\Roadiz\CoreBundle\Form\DataTransformer\TagTranslationDocumentsTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TagTranslationDocumentType extends AbstractType
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            $this->onPostSubmit(...)
        );
        $builder->addModelTransformer(new TagTranslationDocumentsTransformer(
            $this->managerRegistry->getManagerForClass(TagTranslationDocuments::class) ?? throw new \RuntimeException('No manager found for TagTranslationDocuments class.'),
            $options['tagTranslation']
        ));
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'class' => TagTranslationDocuments::class,
        ]);

        $resolver->setRequired('tagTranslation');
        $resolver->setAllowedTypes('tagTranslation', [TagTranslation::class]);
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
        if ($event->getForm()->getConfig()->getOption('tagTranslation') instanceof TagTranslation) {
            $qb = $this->managerRegistry
                ->getRepository(TagTranslationDocuments::class)
                ->createQueryBuilder('ttd');
            $qb->delete()
                ->andWhere($qb->expr()->eq('ttd.tagTranslation', ':tagTranslation'))
                ->setParameter(
                    ':tagTranslation',
                    $event->getForm()->getConfig()->getOption('tagTranslation')
                );
            $qb->getQuery()->execute();
        }
    }
}
