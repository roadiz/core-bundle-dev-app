<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\CoreBundle\Entity\AttributeGroup;
use RZ\Roadiz\CoreBundle\Form\DataTransformer\AttributeGroupTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttributeGroupsType extends AbstractType
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->addModelTransformer(new AttributeGroupTransformer($this->entityManager));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setNormalizer('choices', function (Options $options, $choices) {
            $criteria = [];
            $ordering = [
                'canonicalName' => 'ASC',
            ];
            $attributeGroups = $this->entityManager
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
