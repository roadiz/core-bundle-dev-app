<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Explorer;

use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CustomFormExplorerItem extends AbstractExplorerItem
{
    public function __construct(
        private readonly CustomForm $customForm,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[\Override]
    public function getId(): string|int
    {
        return $this->customForm->getId() ?? throw new \RuntimeException('Entity must have an ID');
    }

    #[\Override]
    public function getAlternativeDisplayable(): ?string
    {
        return strip_tags($this->translator->trans(
            '{0} no.customFormField|{1} 1.customFormField|]1,Inf] %count%.customFormFields',
            [
                '%count%' => $this->customForm->getFields()->count(),
            ]
        ));
    }

    #[\Override]
    public function getDisplayable(): string
    {
        return $this->customForm->getDisplayName();
    }

    #[\Override]
    public function getOriginal(): CustomForm
    {
        return $this->customForm;
    }

    #[\Override]
    protected function getEditItemPath(): ?string
    {
        return $this->urlGenerator->generate('customFormsEditPage', [
            'id' => $this->customForm->getId(),
        ]);
    }

    #[\Override]
    protected function getColor(): ?string
    {
        return $this->customForm->getColor();
    }
}
