<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Form\Constraint\UniqueTagName;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class MultiTagType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('names', TextareaType::class, [
            'label' => 'tags.names',
            'empty_data' => '',
            'attr' => [
                'placeholder' => 'write.every.tags.names.comma.separated',
            ],
            'constraints' => [
                new NotNull(),
                new NotBlank(),
                new UniqueTagName(),
            ],
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'multitags';
    }
}
