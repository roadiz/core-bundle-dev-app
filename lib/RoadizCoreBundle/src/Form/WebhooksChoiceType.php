<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Webhook;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class WebhooksChoiceType extends AbstractType
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    #[\Override]
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'webhooks';
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder->addModelTransformer(new CallbackTransformer(fn (?Webhook $webhook) => $webhook?->getId(), function (int|string|null $id) {
            if (null === $id) {
                return null;
            }

            return $this->managerRegistry->getRepository(Webhook::class)->find($id);
        }));
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        /*
         * Use normalizer to populate choices from ChoiceType
         */
        $resolver->setNormalizer('choices', function () {
            /** @var Webhook[] $webhooks */
            $webhooks = $this->managerRegistry->getRepository(Webhook::class)->findAll();
            $choices = [];
            foreach ($webhooks as $webhook) {
                $choices[(string) $webhook] = $webhook->getId();
            }

            return $choices;
        });
    }
}
