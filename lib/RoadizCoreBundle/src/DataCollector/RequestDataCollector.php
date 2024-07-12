<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DataCollector;

use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequestDataCollector extends AbstractDataCollector
{
    public function __construct(
        private readonly string $cmsVersion,
        private readonly string $cmsVersionPrefix
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Throwable $exception = null): void
    {
        $this->data = [
            'version' => implode(' - ', array_filter([$this->cmsVersionPrefix, $this->cmsVersion])),
        ];
    }

    public function getVersion(): string
    {
        return $this->data['version'];
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
