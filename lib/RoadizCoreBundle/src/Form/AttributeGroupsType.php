<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\AttributeGroup;
use RZ\Roadiz\CoreBundle\Form\DataTransformer\AttributeGroupTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AttributeGroupsType extends AbstractType
{
    public function __construct(
        private readonly AttributeGroupTransformer $attributeGroupTransformer,
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->addModelTransformer($this->attributeGroupTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setNormalizer('choices', function (Options $options, $choices) {
            $criteria = [];
            $ordering = [
                'canonicalName' => 'ASC',
            ];
            $attributeGroups = $this->managerRegistry
                ->getRepository(AttributeGroup::class)
                ->findBy($criteria, $ordering);

            /** @var AttributeGroup $attributeGroup */
            foreach ($attributeGroups as $attributeGroup) {
                $choices[$attributeGroup->getName()] = $attributeGroup->getId();
            }

            return $choices;
        });
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'attribute_groups';
    }
}
