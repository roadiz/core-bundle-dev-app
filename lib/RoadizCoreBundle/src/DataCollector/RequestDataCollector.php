<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DataCollector;

use PackageVersions\Versions;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequestDataCollector extends AbstractDataCollector
{
    private ?string $cmsVersion = null;
    private ?string $cmsVersionPrefix = null;

    public function __construct(string $cmsVersion, string $cmsVersionPrefix)
    {
        $this->cmsVersion = $cmsVersion;
        $this->cmsVersionPrefix = $cmsVersionPrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Throwable $exception = null): void
    {
        $this->data = [];
    }

    public function getVersion(): ?string
    {
        $fallback = implode(' - ', array_filter([$this->cmsVersionPrefix, $this->cmsVersion]));
        if (!class_exists(Versions::class)) {
            return $fallback;
        }

        $version = Versions::getVersion('roadiz/core-bundle');
        preg_match('/^v(.*?)@/', $version, $output);

        return $output[1] ?? strtok($version, '@') ?: $fallback;
    }

    public static function getTemplate(): ?string
    {
        return '@RoadizCore/DataCollector/request.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'roadiz.data_collector.request';
    }

    public function reset(): void
    {
        $this->data = [];
    }
}
