<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Extension that allow render documents Url.
 */
final class DocumentUrlExtension extends AbstractExtension
{
    public function __construct(
        private readonly DocumentUrlGeneratorInterface $documentUrlGenerator,
        private readonly bool $throwExceptions = false,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('url', [$this, 'getUrl']),
        ];
    }

    /**
     * Convert an AbstractEntity to an Url.
     *
     * Compatible AbstractEntity:
     *
     * - Document
     *
     * @throws RuntimeError
     */
    public function getUrl(?PersistableInterface $mixed = null, array $criteria = []): string
    {
        if (null === $mixed) {
            if ($this->throwExceptions) {
                throw new RuntimeError('Twig “url” filter must be used with a not null object');
            }

            return '';
        }

        if ($mixed instanceof DocumentInterface) {
            try {
                $absolute = false;
                if (isset($criteria['absolute'])) {
                    $absolute = (bool) $criteria['absolute'];
                }

                $this->documentUrlGenerator->setOptions($criteria);
                $this->documentUrlGenerator->setDocument($mixed);

                return $this->documentUrlGenerator->getUrl($absolute);
            } catch (InvalidArgumentException $e) {
                throw new RuntimeError($e->getMessage(), -1, null, $e);
            }
        }

        throw new RuntimeError('Twig “url” filter can be only used with a Document');
    }
}
