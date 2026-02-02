<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\CustomForm\Webhook\CustomFormWebhookProviderRegistry;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        /** @var CustomForm|null $customForm */
        $customForm = $options['custom_form'] ?? $builder->getData();

        if (null === $customForm) {
            return;
        }

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
            ->add('webhookFieldMapping', CustomFormWebhookFieldMappingType::class, [
                'custom_form' => $customForm,
                'required' => false,
                'attr' => ['class' => 'rz-form__field-list'],
            ])
            ->add('webhookExtraConfig', CustomFormWebhookExtraConfigType::class, [
                'custom_form' => $customForm,
                'required' => false,
                'attr' => ['class' => 'rz-form__field-list'],
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
            'data_class' => CustomForm::class,
            'custom_form' => null,
            'attr' => [
                'class' => 'rz-form-field__body',
            ],
        ]);

        $resolver->setAllowedTypes('custom_form', ['null', CustomForm::class]);
    }
}
