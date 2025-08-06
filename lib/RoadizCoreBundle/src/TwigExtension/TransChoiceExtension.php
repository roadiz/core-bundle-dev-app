<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use RZ\Roadiz\CoreBundle\TwigExtension\TokenParser\TransChoiceTokenParser;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TokenParser\AbstractTokenParser;
use Twig\TwigFilter;

/**
 * @deprecated Just for BC
 */
final class TransChoiceExtension extends AbstractExtension
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    #[\Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('transchoice', $this->transchoice(...)),
        ];
    }

    /**
     * Returns the token parser instance to add to the existing list.
     *
     * @return AbstractTokenParser[]
     */
    #[\Override]
    public function getTokenParsers(): array
    {
        return [
            // {% transchoice count %}
            //     {0} There is no apples|{1} There is one apple|]1,Inf] There is {{ count }} apples
            // {% endtranschoice %}
            new TransChoiceTokenParser(),
        ];
    }

    /**
     * @deprecated since Symfony 4.2, use the trans() method instead with a %count% parameter
     */
    public function transchoice(
        string $message,
        int $count,
        array $arguments = [],
        ?string $domain = null,
        ?string $locale = null,
    ): string {
        return $this->translator->trans($message, array_merge(['%count%' => $count], $arguments), $domain, $locale);
    }
}
