<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Controller;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait TranslationAwareControllerTrait
{
    abstract protected function getManagerRegistry(): ManagerRegistry;
    abstract protected function getPreviewResolver(): PreviewResolverInterface;

    /**
     * @throws NonUniqueResultException
     */
    protected function getTranslation(Request $request): TranslationInterface
    {
        $locale = $request->query->get('_locale');
        /** @var TranslationRepository $repository */
        $repository = $this->getManagerRegistry()->getRepository(TranslationInterface::class);
        if (!\is_string($locale) || $locale === '') {
            return $repository->findDefault();
        }

        if ($this->getPreviewResolver()->isPreview()) {
            $translation = $repository->findOneByLocaleOrOverrideLocale($locale);
        } else {
            $translation = $repository->findOneAvailableByLocaleOrOverrideLocale($locale);
        }

        if (null !== $translation) {
            return $translation;
        }

        throw new BadRequestHttpException(sprintf('“%s” locale is not available', $locale));
    }
}
