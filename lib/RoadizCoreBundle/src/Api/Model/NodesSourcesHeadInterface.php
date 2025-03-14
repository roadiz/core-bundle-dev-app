<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;

interface NodesSourcesHeadInterface
{
    public function getSiteName(): ?string;

    public function getMetaTitle(): ?string;

    public function getMetaDescription(): ?string;

    public function isNoIndex(): bool;

    public function getShareImage(): ?DocumentInterface;

    public function getTranslation(): TranslationInterface;
}
