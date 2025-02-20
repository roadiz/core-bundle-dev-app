<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This class creates recaptcha element.
 *
 * @author Nikolay Georgiev <symfonist@gmail.com>
 *
 * @since 1.0
 */
final class RecaptchaType extends AbstractType
{
    /**
     * (non-PHPdoc).
     *
     * @see \Symfony\Component\Form\AbstractType::buildView()
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['configs'] = $options['configs'];
    }

    /**
     * @see \Symfony\Component\Form\AbstractType::configureOptions()
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'configs' => [
                'publicKey' => '',
            ],
        ]);
    }

    /**
     * @see \Symfony\Component\Form\AbstractType::getParent()
     */
    public function getParent(): ?string
    {
        return TextType::class;
    }

    /**
     * @see \Symfony\Component\Form\FormTypeInterface::getName()
     *
     *      {% block recaptcha_widget -%}
     *          <div class="g-recaptcha" data-sitekey="{{ configs.publicKey }}"></div>
     *      {%- endblock recaptcha_widget %}
     */
    public function getBlockPrefix(): string
    {
        return 'recaptcha';
    }
}
