<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use Symfony\Component\Form\AbstractType;
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
            $builder->add($field->getName(), TextType::class, [
                'label' => $field->getLabel(),
                'required' => false,
                'help' => sprintf('Map "%s" to external provider field', $field->getName()),
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
            'attr' => ['tag' => 'fieldset'],
        ]);

        $resolver->setAllowedTypes('custom_form', ['null', CustomForm::class]);
    }
}
