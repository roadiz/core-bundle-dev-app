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

    /**
     * @return array{0: string, 1: string, 2: string} [$exactQuery, $fuzzyQuery, $wildcardQuery]
     */
    private function getFormattedQuery(string $q): array
    {
        $handler = $this->createHandler();
        $method = new \ReflectionMethod($handler, 'getFormattedQuery');

        return $method->invoke($handler, $q);
    }

    private function buildQuery(string $q, array $args = []): string
    {
        $handler = $this->createHandler();
        $method = new \ReflectionMethod($handler, 'buildQuery');

        return $method->invokeArgs($handler, [$q, &$args]);
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

    /**
     * Regression test for gitlab.rezo-zero.com/events-api/eventsapi-dev-website#32:
     * a multi-word query must produce a real Lucene PhraseQuery (quoted, with slop),
     * not a single escapeQuery()'d term with the space backslash-escaped away.
     */
    public function testMultiWordQueryBuildsExactPhraseQuery(): void
    {
        [$exactQuery] = $this->getFormattedQuery('King Lear');

        $this->assertSame('"King Lear"~2', $exactQuery);
    }

    public function testExactPhraseQueryEscapesQuotesWithoutBreakingThePhrase(): void
    {
        [$exactQuery] = $this->getFormattedQuery('King "Lear"');

        $this->assertSame('"King \"Lear\""~2', $exactQuery);
    }

    /**
     * Fuzzy clause must require every word (AND), like v7 did, otherwise a single
     * matching word is enough to rank a document (combined with eDismax minimum-match).
     */
    public function testFuzzyQueryRequiresEveryWord(): void
    {
        [, $fuzzyQuery] = $this->getFormattedQuery('King Lear');

        $this->assertSame('(King~2 AND Lear~2)', $fuzzyQuery);
    }

    public function testBuildQueryScopesExactAndFuzzyClausesToTitleField(): void
    {
        $query = $this->buildQuery('King Lear');

        $this->assertStringContainsString('(title:"King Lear"~2)^20', $query);
        $this->assertStringContainsString('(title:(King~2 AND Lear~2))', $query);
    }
}
