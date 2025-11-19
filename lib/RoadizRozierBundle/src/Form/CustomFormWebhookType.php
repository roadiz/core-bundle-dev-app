<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\CustomForm\Webhook\CustomFormWebhookProviderRegistry;
use RZ\Roadiz\CoreBundle\Form\JsonType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Form type for CustomForm webhook configuration.
 */
final class CustomFormWebhookType extends AbstractType
{
    public function __construct(
        private readonly CustomFormWebhookProviderRegistry $providerRegistry,
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('webhookEnabled', CheckboxType::class, [
                'label' => 'customForm.webhook.enabled',
                'help' => 'customForm.webhook.enabled.help',
                'required' => false,
            ])
            ->add('webhookProvider', ChoiceType::class, [
                'label' => 'customForm.webhook.provider',
                'help' => 'customForm.webhook.provider.help',
                'required' => false,
                'placeholder' => 'customForm.webhook.provider.placeholder',
                'choices' => $this->providerRegistry->getProviderChoices(),
            ])
            ->add('webhookFieldMapping', JsonType::class, [
                'label' => 'customForm.webhook.fieldMapping',
                'help' => 'customForm.webhook.fieldMapping.help',
                'required' => false,
                'attr' => [
                    'rows' => 10,
                    'placeholder' => '{"custom_form_field": "provider_field"}',
                ],
                'constraints' => [
                    new Callback(function ($value, ExecutionContextInterface $context) {
                        if (empty($value)) {
                            return;
                        }
                        $decoded = json_decode($value, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            return; // ValidJson constraint will handle this
                        }
                        // Check if JSON structure is only "key-valued" with no sub-objects or sub-arrays
                        if (!is_array($decoded)) {
                            return;
                        }
                        foreach ($decoded as $key => $val) {
                            if (!is_string($key)) {
                                $context->buildViolation('Field mapping must be a key-value object with string keys')
                                    ->addViolation();
                                return;
                            }
                            if (is_array($val) || is_object($val)) {
                                $context->buildViolation('Field mapping must not contain nested objects or arrays')
                                    ->addViolation();
                                return;
                            }
                        }
                    }),
                ],
            ])
            ->add('webhookExtraConfig', JsonType::class, [
                'label' => 'customForm.webhook.extraConfig',
                'help' => 'customForm.webhook.extraConfig.help',
                'required' => false,
                'attr' => [
                    'rows' => 10,
                    'placeholder' => '{"list_id": "123"}',
                ],
            ])
        ;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'customform_webhook';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'customForm.webhook.section',
        ]);
    }
}
