<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Renderer;

use RZ\Roadiz\Documents\Exceptions\InvalidEmbedId;
use RZ\Roadiz\Documents\MediaFinders\EmbedFinderFactory;
use RZ\Roadiz\Documents\Models\BaseDocumentInterface;

class EmbedRenderer implements RendererInterface
{
    public function __construct(protected readonly EmbedFinderFactory $embedFinderFactory)
    {
    }

    #[\Override]
    public function supports(BaseDocumentInterface $document, array $options): bool
    {
        if (
            $document->isEmbed()
            && $this->embedFinderFactory->supports($document->getEmbedPlatform())
            && isset($options['embed'])
            && true === $options['embed']
        ) {
            return true;
        } else {
            return false;
        }
    }

    #[\Override]
    public function render(BaseDocumentInterface $document, array $options): string
    {
        try {
            $finder = $this->embedFinderFactory->createForPlatform(
                $document->getEmbedPlatform(),
                $document->getEmbedId()
            );
            if (null !== $finder) {
                return $finder->getIFrame($options);
            }

            return '';
        } catch (InvalidEmbedId $exception) {
            return '<p>'.$exception->getMessage().'</p>';
        }
    }
}
