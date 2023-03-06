<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class GeoJsonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(function (?array $value) {
            return null !== $value ? json_encode($value) : '';
        }, function (?string $value) {
            return null !== $value ? json_decode($value) : null;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'geojson';
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }
}
