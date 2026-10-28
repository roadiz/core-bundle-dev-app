<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Tests;

use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RZ\Roadiz\SolrBundle\ClientRegistryInterface;
use RZ\Roadiz\SolrBundle\DocumentSearchHandler;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class DocumentSearchHandlerTest extends TestCase
{
    private function createHandler(): DocumentSearchHandler
    {
        return new DocumentSearchHandler(
            $this->createMock(ClientRegistryInterface::class),
            $this->createMock(ObjectManager::class),
            new NullLogger(),
            new EventDispatcher(),
            2,
            3,
        );
    }

    /**
     * `slug_s` is only indexed for node-sources. It resolves through the `*_s`
     * dynamic field, so document search does not fail on it — it just carries a
     * field that can never match, diluting the query. Do not inherit it.
     */
    public function testQueryFieldsDoNotIncludeNodeSourceOnlySlugField(): void
    {
        $handler = $this->createHandler();
        $query = new Query();
        $args = [];
        $method = new \ReflectionMethod($handler, 'configureQueryParser');
        $method->invokeArgs($handler, [$query, 'King Lear', &$args, true]);

        $this->assertSame('title^10 collection_txt^2 tags_txt', $query->getEDisMax()->getQueryFields());
        $this->assertStringNotContainsString('slug_s', $query->getEDisMax()->getQueryFields());
    }
}
