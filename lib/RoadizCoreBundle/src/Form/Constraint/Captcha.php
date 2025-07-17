<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;

final class Captcha extends Constraint
{
    public string $emptyMessage = 'you_must_show_youre_not_robot';
    public string $invalidMessage = 'captcha_is_invalid.try_again';
}
