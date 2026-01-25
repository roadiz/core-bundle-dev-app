<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Form\DataTransformer\NodeTypeTransformer;
use RZ\Roadiz\CoreBundle\Form\NodeTypeFieldsType;
use RZ\Roadiz\CoreBundle\Form\NodeTypesType;
use RZ\Roadiz\RozierBundle\Model\NodeTypeDecoratorPathDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NodeTypeDecoratorPathType extends AbstractType
{
    public function __construct(
        private readonly NodeTypeTransformer $nodeTypeTransformer,
        private readonly DecoratedNodeTypes $decoratedNodeTypes,
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            transform: function (string $path): NodeTypeDecoratorPathDto {
                $pathExploded = explode('.', $path);
                $nodeType = $this->decoratedNodeTypes->get($pathExploded[0]);
                if (null === $nodeType) {
                    throw new TransformationFailedException('Unknown node type '.$pathExploded[0]);
                }
                $field = $nodeType->getFieldByName($pathExploded[1]);

                return new NodeTypeDecoratorPathDto(
                    $nodeType,
                    $field,
                );
            },
            reverseTransform: function (NodeTypeDecoratorPathDto $path): string {
                $nodeType = $path->getNodeType();
                $field = $path->getField();

                return $nodeType->getName().'.'.$field?->getName();
            }
        ));

        if ($options['displayNodeType']) {
            $builder->add('nodeType', NodeTypesType::class, [
                'showInvisible' => true,
            ]);
            $builder->get('nodeType')->addModelTransformer($this->nodeTypeTransformer);
        }

        $builder
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                $form->add('field', NodeTypeFieldsType::class, [
                    'nodeType' => $form->getNormData()?->getNodeType(),
                    'placeholder' => 'nodeTypeDecorator.without_field',
                    'required' => false,
                    'attr' => ['onchange' => 'document.forms["nodetypedecorator"].submit()'],
                ]);
            })
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'data_class' => NodeTypeDecoratorPathDto::class,
            'attr' => [
                'class' => 'uk-form node-type-form',
            ],
            'displayNodeType' => false,
        ]);

        $resolver->setAllowedTypes('displayNodeType', ['bool']);
    }
}
