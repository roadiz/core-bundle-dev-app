<?php

declare(strict_types=1);

namespace Themes\Rozier\Explorer;

use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CustomFormExplorerItem extends AbstractExplorerItem
{
    public function __construct(
        private readonly CustomForm $customForm,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function getId(): string|int
    {
        return $this->customForm->getId();
    }

    public function getAlternativeDisplayable(): ?string
    {
        return strip_tags($this->translator->trans(
            '{0} no.customFormField|{1} 1.customFormField|]1,Inf] %count%.customFormFields',
            [
                '%count%' => $this->customForm->getFields()->count()
            ]
        ));
    }

    public function getDisplayable(): string
    {
        return $this->customForm->getDisplayName();
    }

    public function getOriginal(): CustomForm
    {
        return $this->customForm;
    }

    protected function getEditItemPath(): ?string
    {
        return $this->urlGenerator->generate('customFormsEditPage', [
            'id' => $this->customForm->getId()
        ]);
    }

    protected function getColor(): ?string
    {
        return $this->customForm->getColor();
    }
}
