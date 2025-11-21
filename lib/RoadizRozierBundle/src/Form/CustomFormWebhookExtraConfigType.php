<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\CustomForm\Webhook\CustomFormWebhookProviderRegistry;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Sub-form for CustomForm webhook field mapping.
 * Generates a text field for each CustomFormField.
 */
final class CustomFormWebhookExtraConfigType extends AbstractType
{
    public function __construct(
        private readonly CustomFormWebhookProviderRegistry $providerRegistry,
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var CustomForm|null $customForm */
        $customForm = $options['custom_form'];

        if (null === $customForm || null === $customForm->getWebhookProvider()) {
            return;
        }

        $provider = $this->providerRegistry->getProvider($customForm->getWebhookProvider());
        if (null === $provider) {
            return;
        }

        // Add a text field for each CustomFormField
        foreach ($provider->getConfigSchema() as $fieldName => $field) {
            $type = match ($field['type'] ?? 'text') {
                'choice' => ChoiceType::class,
                'number' => IntegerType::class,
                default => TextType::class,
            };
            $options = match ($field['type'] ?? 'text') {
                'choice' => [
                    'choices' => $field['choices'] ?? [],
                    'placeholder' => $field['placeholder'] ?? null,
                ],
                default => [],
            };
            $builder->add($fieldName, $type, [
                'label' => $field['label'] ?? $fieldName,
                'help' => $field['help'] ?? null,
                'required' => $field['required'] ?? false,
                ...$options,
            ]);
        }
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'customform_webhook_extra_config';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'customForm.webhook.extraConfig',
            'required' => false,
            'attr' => [
                'tag' => 'fieldset',
                'rows' => 10,
                'placeholder' => '{"list_id": "123"}',
            ],
            'custom_form' => null,
        ]);

        $resolver->setAllowedTypes('custom_form', ['null', CustomForm::class]);
    }
}
