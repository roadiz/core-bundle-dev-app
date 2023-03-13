<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\Documents\Models\DocumentInterface;

interface NodesSourcesHeadInterface
{
    public function getSiteName(): ?string;
    public function getMetaTitle(): ?string;
    public function getMetaDescription(): ?string;
    public function getPolicyUrl(): ?string;
    public function getHomePageUrl(): ?string;
    public function isNoIndex(): bool;
    public function getHomePage(): ?NodesSources;
    public function getShareImage(): ?DocumentInterface;
    public function getTranslation(): TranslationInterface;
}
