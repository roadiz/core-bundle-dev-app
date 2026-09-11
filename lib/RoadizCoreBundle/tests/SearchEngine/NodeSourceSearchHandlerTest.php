<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\SearchEngine;

use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RZ\Roadiz\CoreBundle\SearchEngine\ClientRegistry;
use RZ\Roadiz\CoreBundle\SearchEngine\NodeSourceSearchHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class NodeSourceSearchHandlerTest extends TestCase
{
    private function createHandler(): NodeSourceSearchHandler
    {
        return new NodeSourceSearchHandler(
            new ClientRegistry($this->createMock(ContainerInterface::class)),
            $this->createMock(ObjectManager::class),
            new NullLogger(),
            new EventDispatcher()
        );
    }

    /**
     * @param string $q
     * @return array [$exactQuery, $fuzzyQuery, $wildcardQuery]
     */
    private function getFormattedQuery(string $q): array
    {
        $handler = $this->createHandler();
        $method = new \ReflectionMethod($handler, 'getFormattedQuery');

        return $method->invoke($handler, $q);
    }

    /**
     * @param string $q
     * @param array $args
     * @return string
     */
    private function buildQuery(string $q, array $args = []): string
    {
        $handler = $this->createHandler();
        $method = new \ReflectionMethod($handler, 'buildQuery');

        return $method->invokeArgs($handler, [$q, &$args]);
    }

    /**
     * Regression test: a multi-word query must produce a real Lucene PhraseQuery
     * (quoted, with slop), not a single escapeQuery()'d term with the space
     * backslash-escaped away.
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
     * Fuzzy clause must require every word (AND), otherwise a single matching word
     * is enough to rank a document (combined with eDismax minimum-match).
     */
    public function testFuzzyQueryRequiresEveryWord(): void
    {
        [, $fuzzyQuery] = $this->getFormattedQuery('King Lear');

        $this->assertSame('(King~2 AND Lear~2)', $fuzzyQuery);
    }

    public function testBuildQueryScopesExactAndFuzzyClausesToTitleField(): void
    {
        $query = $this->buildQuery('King Lear');

        $this->assertStringContainsString('(title:"King Lear"~2)^10', $query);
        $this->assertStringContainsString('(title:(King~2 AND Lear~2))', $query);
    }
}
