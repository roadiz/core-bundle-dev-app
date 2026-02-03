<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use ApiPlatform\Metadata\ApiResource;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\Documents\Models\BaseDocumentInterface;

#[ApiResource(operations: [])]
interface NodesSourcesHeadInterface
{
    public function getSiteName(): ?string;

    public function getMetaTitle(): ?string;

    public function getMetaDescription(): ?string;

    public function isNoIndex(): bool;

    public function getShareImage(): ?BaseDocumentInterface;

    public function getTranslation(): TranslationInterface;
}
