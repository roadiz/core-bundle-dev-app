<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\CustomForm\Webhook\CustomFormWebhookProviderRegistry;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Form\JsonType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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

        // Add field mapping sub-form dynamically based on CustomForm data
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            // Get CustomForm from the parent form data
            $customForm = null;
            if ($data instanceof CustomForm) {
                $customForm = $data;
            }

            // Add the field mapping sub-form with CustomForm context
            $form->add('webhookFieldMapping', CustomFormWebhookFieldMappingType::class, [
                'custom_form' => $customForm,
                'required' => false,
            ]);
        });
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
