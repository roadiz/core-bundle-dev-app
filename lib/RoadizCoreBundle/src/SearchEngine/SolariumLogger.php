<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use Psr\Log\LoggerInterface;
use Solarium\Core\Client\Endpoint as SolariumEndpoint;
use Solarium\Core\Client\Request as SolariumRequest;
use Solarium\Core\Client\Response as SolariumResponse;
use Solarium\Core\Event\Events as SolariumEvents;
use Solarium\Core\Event\PostExecuteRequest as SolariumPostExecuteRequestEvent;
use Solarium\Core\Event\PreExecuteRequest as SolariumPreExecuteRequestEvent;
use Solarium\Core\Plugin\AbstractPlugin as SolariumPlugin;
use Symfony\Bundle\FrameworkBundle\DataCollector\TemplateAwareDataCollectorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @see https://github.com/nelmio/NelmioSolariumBundle
 */
final class SolariumLogger extends SolariumPlugin implements DataCollectorInterface, \Serializable, EventSubscriberInterface, TemplateAwareDataCollectorInterface
{
    private array $data = [];
    private array $queries = [];
    private ?SolariumRequest $currentRequest = null;
    private ?float $currentStartTime = null;
    private ?SolariumEndpoint $currentEndpoint = null;
    private LoggerInterface $logger;
    private Stopwatch $stopwatch;

    public function __construct(LoggerInterface $searchEngineLogger, Stopwatch $stopwatch)
    {
        parent::__construct();
        $this->logger = $searchEngineLogger;
        $this->stopwatch = $stopwatch;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SolariumEvents::PRE_EXECUTE_REQUEST => ['preExecuteRequest', 1000],
            SolariumEvents::POST_EXECUTE_REQUEST => ['postExecuteRequest', -1000],
        ];
    }

    public function log(
        SolariumRequest $request,
        ?SolariumResponse $response,
        SolariumEndpoint $endpoint,
        float $duration
    ): void {
        $this->queries[] = array(
            'request' => $request,
            'response' => $response,
            'duration' => $duration,
            'base_uri' => $this->getEndpointBaseUrl($endpoint),
        );
    }

    public function collect(HttpRequest $request, HttpResponse $response, \Throwable $exception = null): void
    {
        if (isset($this->currentRequest)) {
            $this->failCurrentRequest();
        }

        $time = 0;
        foreach ($this->queries as $queryStruct) {
            $time += $queryStruct['duration'];
        }
        $this->data = array(
            'queries'     => $this->queries,
            'total_time'  => $time,
        );
    }

    public function getName(): string
    {
        return 'solr';
    }

    public function getQueries(): array
    {
        return array_key_exists('queries', $this->data) ? $this->data['queries'] : [];
    }

    public function getQueryCount(): int
    {
        return count($this->getQueries());
    }

    public function getTotalTime(): int
    {
        return array_key_exists('total_time', $this->data) ? $this->data['total_time'] : 0;
    }

    public function preExecuteRequest(SolariumPreExecuteRequestEvent $event): void
    {
        if (isset($this->currentRequest)) {
            $this->failCurrentRequest();
        }

        $this->stopwatch->start('solr', 'solr');

        $this->currentRequest = $event->getRequest();
        $this->currentEndpoint = $event->getEndpoint();

        $this->logger->debug($this->getEndpointBaseUrl($this->currentEndpoint) . $this->currentRequest->getUri());
        $this->currentStartTime = microtime(true);
    }

    public function postExecuteRequest(SolariumPostExecuteRequestEvent $event): void
    {
        $endTime = microtime(true) - $this->currentStartTime;
        if (!isset($this->currentRequest)) {
            throw new \RuntimeException('Request not set');
        }
        if ($this->currentRequest !== $event->getRequest()) {
            throw new \RuntimeException('Requests differ');
        }

        if ($this->stopwatch->isStarted('solr')) {
            $this->stopwatch->stop('solr');
        }

        $this->log($event->getRequest(), $event->getResponse(), $event->getEndpoint(), $endTime);

        $this->currentRequest = null;
        $this->currentStartTime = null;
        $this->currentEndpoint = null;
    }

    public function failCurrentRequest(): void
    {
        $endTime = microtime(true) - $this->currentStartTime;

        if ($this->stopwatch->isStarted('solr')) {
            $this->stopwatch->stop('solr');
        }

        $this->log($this->currentRequest, null, $this->currentEndpoint, $endTime);

        $this->currentRequest = null;
        $this->currentStartTime = null;
        $this->currentEndpoint = null;
    }

    public function serialize(): string
    {
        return serialize($this->data);
    }

    public function unserialize($data): void
    {
        $this->data = unserialize($data);
    }

    public function reset(): void
    {
        $this->data = [];
        $this->queries = [];
    }

    public function __serialize(): array
    {
        return $this->data;
    }

    public function __unserialize(array $data): void
    {
        $this->data = $data;
    }

    private function getEndpointBaseUrl(SolariumEndpoint $endpoint): string
    {
        // Support for Solarium v4.2: getBaseUri() has been deprecated in favor of getCoreBaseUri()
        return method_exists($endpoint, 'getCoreBaseUri') ? $endpoint->getCoreBaseUri() : $endpoint->getBaseUri();
    }

    public static function getTemplate(): ?string
    {
        return '@RoadizCore/DataCollector/solarium.html.twig';
    }
}
