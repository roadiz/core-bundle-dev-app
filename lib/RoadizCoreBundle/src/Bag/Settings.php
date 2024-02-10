<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Bag;

use RZ\Roadiz\Bag\LazyParameterBag;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Setting;
use RZ\Roadiz\CoreBundle\Repository\DocumentRepository;
use RZ\Roadiz\CoreBundle\Repository\SettingRepository;
use Symfony\Component\Stopwatch\Stopwatch;

final class Settings extends LazyParameterBag
{
    public function __construct(
        private readonly SettingRepository $repository,
        private readonly DocumentRepository $documentRepository,
        private readonly Stopwatch $stopwatch
    ) {
        parent::__construct();
    }

    public function getRepository(): SettingRepository
    {
        return $this->repository;
    }

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
        } catch (\Exception $e) {
            $this->parameters = [];
        }
        $this->ready = true;
        $this->stopwatch->stop('settings');
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = false): mixed
    {
        return parent::get($key, $default);
    }

    /**
     * Get a document from its setting name.
     *
     * @param string $key
     * @return Document|null
     */
    public function getDocument(string $key): ?Document
    {
        try {
            $id = $this->getInt($key);
            return $this->documentRepository->findOneById($id);
        } catch (\Exception $e) {
            return null;
        }
    }
}
