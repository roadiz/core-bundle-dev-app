<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Explorer;

use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class TranslationExplorerItem extends AbstractExplorerItem
{
    public function __construct(
        private readonly Translation $translation,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[\Override]
    protected function getEditItemPath(): string
    {
        return $this->urlGenerator->generate('translationsEditPage', [
            'translationId' => $this->translation->getId(),
        ]);
    }

    #[\Override]
    public function getId(): int
    {
        return $this->translation->getId() ?? throw new \RuntimeException('Translation ID is null');
    }

    #[\Override]
    public function getDisplayable(): string
    {
        return $this->translation->getName();
    }

    #[\Override]
    public function getOriginal(): Translation
    {
        return $this->translation;
    }

    #[\Override]
    public function getAlternativeDisplayable(): string
    {
        return $this->translation->getLocale();
    }
}
