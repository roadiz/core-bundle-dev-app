<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class TranstypeType extends AbstractType
{
    protected ManagerRegistry $managerRegistry;

    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly NodeTypes $nodeTypesbag,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'nodeTypeName',
            ChoiceType::class,
            [
                'choices' => $this->getAvailableTypes($options['currentType']),
                'label' => 'nodeType',
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'transtype';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'compound' => true,
            'label' => false,
            'nodeName' => null,
            'attr' => [
                'class' => 'uk-form transtype-form',
            ],
        ]);

        $resolver->setRequired([
            'currentType',
        ]);
        $resolver->setAllowedTypes('currentType', NodeType::class);
    }

    protected function getAvailableTypes(NodeType $currentType): array
    {
        $nodeTypes = $this->nodeTypesbag->all();

        $result = array_values(array_filter(array_map(static function (NodeType $nodeType) use ($currentType) {
            return ($nodeType->getName() !== $currentType->getName()) ? $nodeType->getName() : null;
        }, $nodeTypes)));

        return array_combine($result, $result);
    }
}
