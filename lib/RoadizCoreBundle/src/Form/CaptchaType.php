<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Captcha\CaptchaServiceInterface;
use RZ\Roadiz\CoreBundle\Form\Constraint\Captcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CaptchaType extends AbstractType
{
    public function __construct(
        private readonly CaptchaServiceInterface $captchaService,
    ) {
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Symfony\Component\Form\AbstractType::buildView()
     */
    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['configs'] = $options['configs'];
    }

    /**
     * @see \Symfony\Component\Form\AbstractType::configureOptions()
     */
    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'configs' => [
                'publicKey' => $this->captchaService->getPublicKey(),
            ],
            'constraints' => [
                new Captcha(),
            ],
        ]);
    }

    #[\Override]
    public function getParent(): ?string
    {
        return TextType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return $this->captchaService->getFormWidgetName();
    }
}
