<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @see https://developers.google.com/recaptcha/docs/verify
 */
final class Recaptcha extends Constraint
{
    public const FORM_NAME = 'g-recaptcha-response';
    public string $emptyMessage = 'you_must_show_youre_not_robot';
    public string $invalidMessage = 'recaptcha_is_invalid.try_again';
    public string $fieldName = self::FORM_NAME;
    public string $privateKey = '';
    public string $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * @return string[]
     */
    public function getRequiredOptions(): array
    {
        return [
            'privateKey',
        ];
    }
}
