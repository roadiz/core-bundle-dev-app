<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DataCollector;

use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AutoconfigureTag('data_collector', [
    'template' => '@RoadizCore/DataCollector/request.html.twig',
    # must match the value returned by the getName() method
    'id' => 'roadiz.data_collector.request',
    'priority' => 400,
])]
final class RequestDataCollector extends AbstractDataCollector
{
    public function __construct(
        #[Autowire('%roadiz_core.cms_version%')]
        private readonly string $cmsVersion,
        #[Autowire('%roadiz_core.cms_version_prefix%')]
        private readonly string $cmsVersionPrefix
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Throwable $exception = null): void
    {
        $this->data = [
            'version' => implode(' - ', [$this->cmsVersionPrefix, $this->cmsVersion])
        ];
    }

    public function getVersion(): string
    {
        return $this->data['version'] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'roadiz.data_collector.request';
    }
}
