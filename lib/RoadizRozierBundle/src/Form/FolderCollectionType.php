<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Form\DataTransformer\FolderCollectionTransformer;
use RZ\Roadiz\RozierBundle\Explorer\Provider\FoldersProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FolderCollectionType extends AbstractType
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['provider_class'] = FoldersProvider::class;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'class' => Folder::class,
            'multiple' => true,
            'property' => 'id',
        ]);
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new FolderCollectionTransformer(
            $this->managerRegistry->getManager(),
            true
        ));
    }

    #[\Override]
    public function getParent(): ?string
    {
        return TextType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'folders';
    }
}
