<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            $fieldName = $field->getName();
            $builder->add($fieldName, TextType::class, [
                'label' => $field->getLabel(),
                'required' => false,
                'attr' => [
                    'placeholder' => 'provider_field_name',
                ],
                'help' => sprintf('Map "%s" to external provider field', $field->getName()),
            ]);
        }

        // Add model transformer to convert array to/from JSON
        $builder->addModelTransformer(new CallbackTransformer(
            // Transform JSON string from database to array for the form
            function ($jsonString) {
                if (empty($jsonString)) {
                    return [];
                }
                if (is_array($jsonString)) {
                    return $jsonString;
                }
                $decoded = json_decode($jsonString, true);

                return is_array($decoded) ? $decoded : [];
            },
            // Transform array from form back to JSON string for database
            function ($array) {
                if (empty($array) || !is_array($array)) {
                    return null;
                }
                // Filter out empty values
                $filtered = array_filter($array, fn ($value) => !empty($value));

                return empty($filtered) ? null : json_encode($filtered);
            }
        ));
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
        ]);

        $resolver->setAllowedTypes('custom_form', ['null', CustomForm::class]);
    }
}
