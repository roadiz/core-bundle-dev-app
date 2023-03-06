<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

interface FileHashInterface
{
    public function setFileHash(?string $hash): static;
    public function getFileHash(): ?string;
    public function setFileHashAlgorithm(?string $algorithm): static;
    public function getFileHashAlgorithm(): ?string;
}
