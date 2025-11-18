<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\CustomForm\Webhook\CustomFormWebhookProviderRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
            ->add('webhookFieldMapping', TextareaType::class, [
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
                            $context->buildViolation('Invalid JSON format')
                                ->addViolation();
                        }
                    }),
                ],
            ])
            ->add('webhookExtraConfig', TextareaType::class, [
                'label' => 'customForm.webhook.extraConfig',
                'help' => 'customForm.webhook.extraConfig.help',
                'required' => false,
                'attr' => [
                    'rows' => 10,
                    'placeholder' => '{"list_id": "123"}',
                ],
                'constraints' => [
                    new Callback(function ($value, ExecutionContextInterface $context) {
                        if (empty($value)) {
                            return;
                        }
                        $decoded = json_decode($value, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $context->buildViolation('Invalid JSON format')
                                ->addViolation();
                        }
                    }),
                ],
            ])
        ;

        // Transform array to JSON string and vice versa for webhookFieldMapping
        $builder->get('webhookFieldMapping')
            ->addModelTransformer(new CallbackTransformer(
                function ($array) {
                    // Transform array to string for the form
                    return is_array($array) ? json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $array;
                },
                function ($string) {
                    // Transform string back to array for the model
                    if (empty($string)) {
                        return null;
                    }

                    return json_decode($string, true);
                }
            ));

        // Transform array to JSON string and vice versa for webhookExtraConfig
        $builder->get('webhookExtraConfig')
            ->addModelTransformer(new CallbackTransformer(
                function ($array) {
                    // Transform array to string for the form
                    return is_array($array) ? json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $array;
                },
                function ($string) {
                    // Transform string back to array for the model
                    if (empty($string)) {
                        return null;
                    }

                    return json_decode($string, true);
                }
            ));
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
