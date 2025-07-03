<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Form\Constraint\UniqueFilename;
use RZ\Roadiz\CoreBundle\Form\DocumentCollectionType;
use RZ\Roadiz\CoreBundle\Form\JsonType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;

class DocumentAlignmentType extends AbstractType
{

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('imageCropAlignment', HiddenType::class, [
            'label' => 'document.imageCropAlignment',
            'help' => 'document.imageCropAlignment.help',
            'required' => false,
        ]);
        $builder->add('hotspot', HiddenType::class, [
            'label' => 'document.hotspot',
            'help' => 'document.hotspot.help',
            'required' => false,
        ]);
        $builder->get('hotspot')
        ->addModelTransformer(new CallbackTransformer(
            fn (mixed $hotspot): string => json_encode($hotspot, JSON_THROW_ON_ERROR),
            function (mixed $hotspot): ?array {
                if (!\is_string($hotspot)) {
                    return null;
                }
                try {
                    return json_decode($hotspot, true, flags: JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new TransformationFailedException($e->getMessage(), previous: $e);
                }
            }
        ));

    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
            'inherit_data' => true,
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'document_alignment';
    }
}
