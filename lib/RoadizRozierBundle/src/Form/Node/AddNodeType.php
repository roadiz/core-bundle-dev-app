<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form\Node;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Form\DataTransformer\NodeTypeTransformer;
use RZ\Roadiz\CoreBundle\Form\NodeStatesType;
use RZ\Roadiz\CoreBundle\Form\NodeTypesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

final class AddNodeType extends AbstractType
{
    public function __construct(private readonly NodeTypeTransformer $nodeTypeTransformer)
    {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class, [
            'label' => 'title',
            'empty_data' => '',
            'mapped' => false,
            'constraints' => [
                new NotNull(),
                new NotBlank(),
                new Length([
                    'max' => 255,
                ]),
            ],
        ]);

        if (true === $options['showNodeType']) {
            $builder->add('nodeTypeName', NodeTypesType::class, [
                'label' => 'nodeType',
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
            ]);
            $builder->get('nodeTypeName')->addModelTransformer($this->nodeTypeTransformer);
        }

        $builder->add('dynamicNodeName', CheckboxType::class, [
            'label' => 'node.dynamicNodeName',
            'required' => false,
            'help' => 'dynamic_node_name_will_follow_any_title_change_on_default_translation',
        ]);
        $builder->add('visible', CheckboxType::class, [
            'label' => 'node.visible',
            'help' => 'node.visible.help',
            'required' => false,
        ]);
        $builder->add('hideChildren', CheckboxType::class, [
            'label' => 'node.hideChildren',
            'help' => 'node.hideChildren.help',
            'required' => false,
        ]);
        $builder->add('shadow', CheckboxType::class, [
            'label' => 'node.shadow',
            'help' => 'node.shadow.help',
            'required' => false,
        ]);
        $builder->add('locked', CheckboxType::class, [
            'label' => 'node.locked',
            'help' => 'node.locked.help',
            'required' => false,
        ]);
        $builder->add('status', NodeStatesType::class, [
            'label' => 'node.status',
            'required' => true,
        ]);

        $builder->addEventListener(FormEvents::SUBMIT, function (SubmitEvent $event) {
            $node = $event->getData();
            $form = $event->getForm();

            if (!isset($form['title'])) {
                throw new \RuntimeException('title is not submitted');
            }

            if (!$node instanceof Node) {
                throw new \RuntimeException('Data is not a Node');
            }

            /*
             * Already set Node name before data validation stage.
             */
            $node->setNodeName($form['title']->getData() ?? '');
            $event->setData($node);
        });
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'childnode';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Node::class,
            'label' => false,
            'nodeName' => '',
            'showNodeType' => true,
            'attr' => [
                'class' => 'uk-form childnode-form',
            ],
        ]);

        $resolver->setAllowedTypes('nodeName', 'string');
        $resolver->setAllowedTypes('showNodeType', 'boolean');
    }
}
