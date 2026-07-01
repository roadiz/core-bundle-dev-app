<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Tests;

use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\SolrBundle\ClientRegistryInterface;
use RZ\Roadiz\SolrBundle\NodeSourceSearchHandler;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class NodeSourceSearchHandlerTest extends TestCase
{
    private function createHandler(): NodeSourceSearchHandler
    {
        return new NodeSourceSearchHandler(
            $this->createMock(ClientRegistryInterface::class),
            $this->createMock(ObjectManager::class),
            new NullLogger(),
            new EventDispatcher(),
            2,
            3,
        );
    }

    private function argFqProcess(array $args): array
    {
        $handler = $this->createHandler();
        $method = new \ReflectionMethod($handler, 'argFqProcess');

        return $method->invokeArgs($handler, [&$args]);
    }

    public function testDefaultCriteriaExcludesEmbargoedContent(): void
    {
        $args = $this->argFqProcess([]);

        $this->assertContains('node_status_i:'.NodeStatus::PUBLISHED->value, $args['fq']);
        $this->assertContains('published_at_dt:[* TO NOW/MINUTE]', $args['fq']);
    }

    public function testExplicitPublishedAtFilterIsNotDuplicated(): void
    {
        $publishedAt = new \DateTime('2026-01-01T00:00:00Z');
        $args = $this->argFqProcess([
            'publishedAt' => ['<=', $publishedAt],
        ]);

        $this->assertContains('node_status_i:'.NodeStatus::PUBLISHED->value, $args['fq']);
        $this->assertContains('published_at_dt:[* TO 2026-01-01T00:00:00Z]', $args['fq']);
        $this->assertNotContains('published_at_dt:[* TO NOW/MINUTE]', $args['fq']);
    }

    public function testExplicitStatusOverrideSkipsAutomaticTemporalFilter(): void
    {
        $args = $this->argFqProcess([
            'status' => ['<=', NodeStatus::ARCHIVED],
        ]);

        $this->assertContains('node_status_i:[* TO '.NodeStatus::ARCHIVED->value.']', $args['fq']);
        $this->assertNotContains('published_at_dt:[* TO NOW/MINUTE]', $args['fq']);
    }
}
