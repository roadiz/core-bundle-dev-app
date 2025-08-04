<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Widget;

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
    protected ?TranslationInterface $defaultTranslation = null;

    public function __construct(
        protected RequestStack $requestStack,
        protected ManagerRegistry $managerRegistry,
    ) {
    }

    protected function getRequest(): Request
    {
        $request = $this->requestStack->getCurrentRequest() ?? $this->requestStack->getMainRequest();
        if (null === $request) {
            throw new \RuntimeException('Request cannot be found.');
        }

        return $request;
    }

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
