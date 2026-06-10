<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityHandler;

use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\Core\Handlers\AbstractHandler;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Cache\ResettableInterface;

/**
 * Handle operations with translations entities.
 */
final class TranslationHandler extends AbstractHandler
{
    private ?TranslationInterface $translation = null;

    /**
     * @return $this
     */
    public function setTranslation(TranslationInterface $translation): static
    {
        $this->translation = $translation;

        return $this;
    }

    /**
     * Set current translation as default one.
     *
     * @return $this
     */
    public function makeDefault(): static
    {
        $defaults = $this->objectManager
            ->getRepository(Translation::class)
            ->findBy(['defaultTranslation' => true]);

        /** @var TranslationInterface $default */
        foreach ($defaults as $default) {
            $default->setDefaultTranslation(false);
        }
        $this->objectManager->flush();
        $this->translation?->setDefaultTranslation(true);
        $this->objectManager->flush();

        if ($this->objectManager instanceof EntityManagerInterface) {
            $resultCache = $this->objectManager->getConfiguration()->getResultCache();
            $resultCache?->clear();
            if ($resultCache instanceof ResettableInterface) {
                $resultCache->reset();
            }
        }

        return $this;
    }
}
