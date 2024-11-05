<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use Doctrine\ORM\ORMException;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Translation\TranslationViewer;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class TranslationMenuExtension extends AbstractExtension
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TranslationViewer $translationViewer,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('menu', [$this, 'getMenuAssignation']),
        ];
    }

    /**
     * @throws ORMException
     */
    public function getMenuAssignation(?TranslationInterface $translation = null, bool $absolute = false): array
    {
        if (null !== $translation) {
            $this->translationViewer->setTranslation($translation);

            return $this->translationViewer->getTranslationMenuAssignation($this->requestStack->getCurrentRequest(), $absolute);
        } else {
            return [];
        }
    }
}
