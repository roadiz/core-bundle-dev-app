<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Bag;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Bag\LazyParameterBag;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Setting;
use RZ\Roadiz\CoreBundle\Repository\SettingRepository;
use Symfony\Component\Stopwatch\Stopwatch;

final class Settings extends LazyParameterBag
{
    private ?SettingRepository $repository = null;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly Stopwatch $stopwatch,
    ) {
        parent::__construct();
    }

    public function getRepository(): SettingRepository
    {
        if (null === $this->repository) {
            $this->repository = $this->managerRegistry->getRepository(Setting::class);
        }

        return $this->repository;
    }

    #[\Override]
    protected function populateParameters(): void
    {
        $this->stopwatch->start('settings');
        try {
            $settings = $this->getRepository()->findAll();
            $this->parameters = [];
            /** @var Setting $setting */
            foreach ($settings as $setting) {
                $this->parameters[$setting->getName()] = $setting->getValue();
            }
        } catch (\Exception) {
            $this->parameters = [];
        }
        $this->ready = true;
        $this->stopwatch->stop('settings');
    }

    #[\Override]
    public function get(string $key, $default = false): mixed
    {
        return parent::get($key, $default);
    }

    /**
     * Get a document from its setting name.
     */
    public function getDocument(string $key): ?Document
    {
        try {
            $id = $this->getInt($key);

            return $this->managerRegistry
                        ->getRepository(Document::class)
                        ->findOneById($id);
        } catch (\Exception) {
            return null;
        }
    }
}
