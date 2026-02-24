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
     * @see AbstractType::buildView()
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['configs'] = $options['configs'];
    }

    /**
     * @see AbstractType::configureOptions()
     */
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

    public function getParent(): ?string
    {
        return TextType::class;
    }

    public function getBlockPrefix(): string
    {
        return $this->captchaService->getFormWidgetName();
    }
}
