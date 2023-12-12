<?php

declare(strict_types=1);

namespace Themes\Rozier\Widgets;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Exception\NoTranslationAvailableException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * A widget always has to be created and called from a valid AppController
 * in order to get Twig renderer engine, security context and request context.
 */
abstract class AbstractWidget
{
    protected RequestStack $requestStack;
    protected ManagerRegistry $managerRegistry;
    protected ?TranslationInterface $defaultTranslation = null;

    /**
     * @param RequestStack $requestStack
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(RequestStack $requestStack, ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
        $this->requestStack = $requestStack;
    }

    /**
     * @return Request
     */
    protected function getRequest(): Request
    {
        $request = $this->requestStack->getCurrentRequest() ?? $this->requestStack->getMainRequest();
        if (null === $request) {
            throw new \RuntimeException('Request cannot be found.');
        }
        return $request;
    }

    /**
     * @return ManagerRegistry
     */
    protected function getManagerRegistry(): ManagerRegistry
    {
        return $this->managerRegistry;
    }

    protected function getTranslation(): TranslationInterface
    {
        if (null === $this->defaultTranslation) {
            $this->defaultTranslation = $this->getManagerRegistry()
                ->getRepository(Translation::class)
                ->findDefault();

            if (null === $this->defaultTranslation) {
                throw new NoTranslationAvailableException();
            }
        }

        return $this->defaultTranslation;
    }
}
