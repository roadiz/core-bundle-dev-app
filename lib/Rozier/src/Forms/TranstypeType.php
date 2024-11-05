<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms;

use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
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

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'nodeTypeId',
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
        $qb = $this->managerRegistry->getManager()->createQueryBuilder();
        $qb->select('n')
           ->from(NodeType::class, 'n')
           ->where($qb->expr()->neq('n.id', $currentType->getId()))
           ->orderBy('n.displayName', 'ASC');

        try {
            $types = $qb->getQuery()->getResult();

            $choices = [];
            /** @var NodeType $type */
            foreach ($types as $type) {
                $choices[$type->getDisplayName()] = $type->getId();
            }

            return $choices;
        } catch (NoResultException $e) {
            return [];
        }
    }
}
