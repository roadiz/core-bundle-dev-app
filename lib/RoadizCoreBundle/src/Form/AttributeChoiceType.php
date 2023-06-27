<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\CoreBundle\Entity\Attribute;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttributeChoiceType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function ($dataToForm) {
                if ($dataToForm instanceof Attribute) {
                    return $dataToForm->getId();
                }
                return null;
            },
            function ($formToData) use ($options) {
                return $options['entityManager']->find(Attribute::class, $formToData);
            }
        ));
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('empty_data', null);
        $resolver->setRequired('entityManager');
        $resolver->setAllowedTypes('entityManager', [EntityManagerInterface::class]);
        $resolver->setRequired('translation');
        $resolver->setAllowedTypes('translation', [Translation::class]);
        $resolver->setNormalizer('choices', function (Options $options) {
            $choices = [];
            /** @var Attribute[] $attributes */
            $attributes = $options['entityManager']->getRepository(Attribute::class)->findBy(
                [],
                ['code' => 'ASC']
            );
            foreach ($attributes as $attribute) {
                $label = $attribute->getLabelOrCode($options['translation']);
                if (
                    null !== $attribute->getGroup() &&
                    null !== $groupName = $attribute->getGroup()->getName()
                ) {
                    if (!isset($choices[$groupName]) || !is_array($choices[$groupName])) {
                        $choices[$groupName] = [];
                    }
                    $choices[$groupName][$label] = $attribute->getId();
                } else {
                    $choices[$label] = $attribute->getId();
                }
            }
            return $choices;
        });
    }

    /**
     * @inheritDoc
     */
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
