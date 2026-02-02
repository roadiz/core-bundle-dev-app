<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Sub-form for CustomForm webhook field mapping.
 * Generates a text field for each CustomFormField.
 */
final class CustomFormWebhookFieldMappingType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var CustomForm|null $customForm */
        $customForm = $options['custom_form'];

        if (null === $customForm) {
            return;
        }

        // Add a text field for each CustomFormField
        foreach ($customForm->getFields() as $field) {
            $builder->add($field->getName(), TextType::class, [
                'label' => $field->getLabel(),
                'required' => false,
                'attr' => [
                    'placeholder' => 'provider_field_name',
                ],
                'help' => sprintf('Map "%s" to external provider field', $field->getName()),
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Provider field name cannot be longer than {{ limit }} characters',
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9._-]*$/',
                        'message' => 'Provider field name can only contain alphanumeric characters, dots, hyphens, and underscores',
                    ]),
                ],
            ]);
        }
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'customform_webhook_field_mapping';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'customForm.webhook.fieldMapping',
            'custom_form' => null,
            'attr' => [
                'class' => 'rz-fieldset rz-form-field__body',
            ],
        ]);

        $resolver->setAllowedTypes('custom_form', ['null', CustomForm::class]);
    }
}
