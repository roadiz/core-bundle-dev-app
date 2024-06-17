<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Attribute;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AttributeChoiceType extends AbstractType
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

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
            function ($formToData) {
                return $this->managerRegistry->getRepository(Attribute::class)->find($formToData);
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
        $resolver->setRequired('translation');
        $resolver->setAllowedTypes('translation', [Translation::class]);
        $resolver->setNormalizer('choices', function (Options $options) {
            $choices = [];
            /** @var Attribute[] $attributes */
            $attributes = $this->managerRegistry->getRepository(Attribute::class)->findBy(
                [],
                ['code' => 'ASC']
            );
            foreach ($attributes as $attribute) {
                if (null !== $attribute->getGroup()) {
                    if (!isset($choices[$attribute->getGroup()->getName()])) {
                        $choices[$attribute->getGroup()->getName()] = [];
                    }
                    $choices[$attribute->getGroup()->getName()][$attribute->getLabelOrCode($options['translation'])] = $attribute->getId();
                } else {
                    $choices[$attribute->getLabelOrCode($options['translation'])] = $attribute->getId();
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
