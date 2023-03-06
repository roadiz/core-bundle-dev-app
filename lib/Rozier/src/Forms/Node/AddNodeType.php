<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Form\DataTransformer\NodeTypeTransformer;
use RZ\Roadiz\CoreBundle\Form\NodeTypesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Event\SubmitEvent;

/**
 * @package Themes\Rozier\Forms\Node
 */
class AddNodeType extends AbstractType
{
    protected ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
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
                    'max' => 255
                ])
            ],
        ]);

        if ($options['showNodeType'] === true) {
            $builder->add('nodeType', NodeTypesType::class, [
                'label' => 'nodeType',
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
            ]);
            $builder->get('nodeType')->addModelTransformer(new NodeTypeTransformer(
                $this->managerRegistry->getManager()
            ));
        }

        $builder->add('dynamicNodeName', CheckboxType::class, [
            'label' => 'node.dynamicNodeName',
            'required' => false,
            'help' => 'dynamic_node_name_will_follow_any_title_change_on_default_translation',
        ])
        ->add('visible', CheckboxType::class, [
            'label' => 'visible',
            'required' => false,
        ])
        ->add('locked', CheckboxType::class, [
            'label' => 'locked',
            'required' => false,
        ])
        ->add('hideChildren', CheckboxType::class, [
            'label' => 'hiding-children',
            'required' => false,
        ])
        ->add('status', ChoiceType::class, [
            'label' => 'node.status',
            'required' => true,
            'choices' => [
                Node::getStatusLabel(Node::DRAFT) => Node::DRAFT,
                Node::getStatusLabel(Node::PENDING) => Node::PENDING,
                Node::getStatusLabel(Node::PUBLISHED) => Node::PUBLISHED,
                Node::getStatusLabel(Node::ARCHIVED) => Node::ARCHIVED,
            ],
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

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'childnode';
    }

    /**
     * @param OptionsResolver $resolver
     */
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
