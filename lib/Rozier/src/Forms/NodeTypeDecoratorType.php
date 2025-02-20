<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms;

use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeDecorator;
use RZ\Roadiz\CoreBundle\Enum\NodeTypeDecoratorProperty;
use RZ\Roadiz\CoreBundle\Form\ColorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NodeTypeDecoratorType extends AbstractType
{
    public function __construct(
        private readonly DecoratedNodeTypes $decoratedNodeTypes,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('path', NodeTypeDecoratorPathType::class)
            ->add('property', EnumType::class, [
                'class' => NodeTypeDecoratorProperty::class,
                'choice_loader' => new CallbackChoiceLoader(function () use ($builder) {
                    /** @var NodeTypeDecorator $data */
                    $data = $builder->getData();
                    $field = $this->getExplodedPath($data->getPath())['field'] ?? null;

                    return array_filter(NodeTypeDecoratorProperty::cases(), function (NodeTypeDecoratorProperty $property) use ($field) {
                        return (null !== $field) ? !$property->isNodeTypeProperty() : $property->isNodeTypeProperty();
                    });
                }),
                'choice_label' => fn (NodeTypeDecoratorProperty $property) => $property->value,
                'label' => 'nodeTypeDecorator.property',
                'required' => true,
                'attr' => ['onchange' => 'document.forms["nodetypedecorator"].submit()'],
            ])
            ->add('value', $this->getValueType($builder->getData()), [
                'label' => 'nodeTypeDecorator.value',
                'required' => false,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'nodetypedecorator';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'name' => '',
            'data_class' => NodeTypeDecorator::class,
            'attr' => [
                'class' => 'uk-form node-type-form',
            ],
        ]);
    }

    private function getValueType(NodeTypeDecorator $nodeTypeDecorator): string
    {
        $property = $nodeTypeDecorator->getProperty();
        if ($property->isIntegerType()) {
            return IntegerType::class;
        } elseif ($property->isBooleanType()) {
            return CheckboxType::class;
        } elseif (NodeTypeDecoratorProperty::NODE_TYPE_COLOR === $property) {
            return ColorType::class;
        } else {
            return TextType::class;
        }
    }

    private function getExplodedPath(string $path): array
    {
        $pathExploded = explode('.', $path);
        $nodeType = $this->decoratedNodeTypes->get($pathExploded[0]);
        $field = $nodeType->getFieldByName($pathExploded[1]);

        return [
            'nodeType' => $nodeType,
            'field' => $field,
        ];
    }
}
