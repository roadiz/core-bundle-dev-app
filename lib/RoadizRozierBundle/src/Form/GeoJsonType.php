<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class GeoJsonType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(fn (mixed $value) => null !== $value ? json_encode($value) : '', fn (?string $value) => null !== $value ? json_decode($value) : null));
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'geojson';
    }

    #[\Override]
    public function getParent(): string
    {
        return TextareaType::class;
    }
}
