<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use RZ\Roadiz\CoreBundle\Entity\Theme;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

interface NodeUrlMatcherInterface extends UrlMatcherInterface, RequestMatcherInterface
{
    /**
     * @return array<string>
     */
    public function getSupportedFormatExtensions(): array;

    public function getDefaultSupportedFormatExtension(): string;

    public function matchNode(string $decodedUrl, ?Theme $theme): array;
}
