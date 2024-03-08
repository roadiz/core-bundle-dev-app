<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use League\Flysystem\FilesystemOperator;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use RZ\Roadiz\Documents\UrlGenerators\AbstractDocumentUrlGenerator;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class DocumentUrlGenerator extends AbstractDocumentUrlGenerator
{
    public function __construct(
        FilesystemOperator $documentsStorage,
        UrlHelper $urlHelper,
        private UrlGeneratorInterface $urlGenerator,
        CacheItemPoolInterface $optionsCacheAdapter
    ) {
        parent::__construct($documentsStorage, $urlHelper, $optionsCacheAdapter);
    }

    /**
     * @return string
     */
    protected function getRouteName(): string
    {
        return 'interventionRequestProcess';
    }

    protected function getProcessedDocumentUrlByArray(bool $absolute = false): string
    {
        if (null === $this->getDocument()) {
            throw new \InvalidArgumentException('Cannot get URL from a NULL document');
        }

        $referenceType = $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH;

        $routeParams = [
            'queryString' => $this->optionCompiler->compile($this->options),
            'filename' => $this->getDocument()->getRelativePath(),
        ];

        return $this->urlGenerator->generate(
            $this->getRouteName(),
            $routeParams,
            $referenceType
        );
    }
}
