<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Tests;

use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\SolrBundle\ClientRegistryInterface;
use RZ\Roadiz\SolrBundle\NodeSourceSearchHandler;
use Solarium\Component\EdisMax;
use Solarium\QueryType\Select\Query\Query;
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

    private function buildQuery(string $q, array $args = []): string
    {
        $handler = $this->createHandler();
        $method = new \ReflectionMethod($handler, 'buildQuery');

        return $method->invokeArgs($handler, [$q, &$args]);
    }

    private function buildFuzzyQuery(string $q): string
    {
        $handler = $this->createHandler();
        $method = new \ReflectionMethod($handler, 'buildFuzzyQuery');

        return $method->invoke($handler, $q);
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function getFormattedQuery(string $q): array
    {
        $handler = $this->createHandler();
        $method = new \ReflectionMethod($handler, 'getFormattedQuery');

        return $method->invoke($handler, $q);
    }

    private function configuredQuery(array $args = [], bool $searchTags = false): Query
    {
        $handler = $this->createHandler();
        $query = new Query();
        $method = new \ReflectionMethod($handler, 'configureQueryParser');
        $method->invokeArgs($handler, [$query, &$args, $searchTags]);

        return $query;
    }

    private function configuredEdisMax(array $args = [], bool $searchTags = false): EdisMax
    {
        return $this->configuredQuery($args, $searchTags)->getEDisMax();
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
     * Under eDisMax the query string carries terms only: no field prefix, no
     * hand-built per-field clauses. Fields come from `qf`/`pf`.
     *
     * Terms stay plain so Solr analyses them. A fuzzy term is a MultiTermQuery,
     * which skips the field analyzer: stopwords then survive and `mm` keeps
     * requiring them, so "Pas de page sur la choucroute" matched nothing while
     * the page titled exactly that sat in the index.
     */
    public function testBuildQueryKeepsTermsAnalysableWithoutFieldPrefix(): void
    {
        $this->assertSame('King Lear', $this->buildQuery('King Lear'));
        $this->assertSame('Pas de page sur la choucroute', $this->buildQuery('Pas de page sur la choucroute'));
    }

    /**
     * Typo tolerance moves to the second pass, run only when the analysed one
     * came back empty.
     */
    public function testFuzzyQueryFuzzifiesEveryLongEnoughWord(): void
    {
        $this->assertSame('King~2 Lear~2', $this->buildFuzzyQuery('King Lear'));
    }

    public function testShortWordsAreNotFuzzified(): void
    {
        $this->assertSame('Le roi~2 Lear~2', $this->buildFuzzyQuery('Le roi Lear'));
    }

    public function testQueryIsParsedByEdisMax(): void
    {
        $this->assertSame('edismax', $this->configuredEdisMax()->getQueryParser());
    }

    /**
     * Regression test for gitlab.rezo-zero.com/events-api/eventsapi-dev-website#32:
     * a multi-word query must still boost documents matching the words as a
     * phrase. That is now eDisMax `pf`/`ps` instead of a hand-built PhraseQuery.
     */
    public function testPhraseBoostIsDeclaredOnTitleAndCollection(): void
    {
        $edisMax = $this->configuredEdisMax();

        $this->assertSame('title^20 collection_txt^2', $edisMax->getPhraseFields());
        $this->assertSame(2, $edisMax->getPhraseSlop());
    }

    /**
     * Every word must be required, otherwise a single matching word is enough to
     * rank a document. That is `mm` now, no longer an explicit AND join.
     */
    public function testMinimumMatchRequiresEveryWord(): void
    {
        $this->assertSame('100%', $this->configuredEdisMax()->getMinimumMatch());
    }

    /**
     * …but `qf` also holds raw `string` fields (`slug_s`) which, unlike the text
     * ones, keep stopwords. Without autoRelax, mm=100% made every query holding
     * one ("le roi Lear") require a slug literally equal to "le": zero hits.
     */
    public function testMinimumMatchIsRelaxedWhenAnalysisDropsWords(): void
    {
        $this->assertSame('true', $this->configuredQuery()->getParams()['mm.autoRelax']);
    }

    public function testQueryFieldsIncludeSlugAndSkipTagsUnlessAsked(): void
    {
        $this->assertSame(
            'title^10 collection_txt^2 slug_s',
            $this->configuredEdisMax()->getQueryFields()
        );
        $this->assertSame(
            'title^10 collection_txt^2 tags_txt slug_s',
            $this->configuredEdisMax(searchTags: true)->getQueryFields()
        );
    }

    public function testQueryFieldsFollowRequestedLocale(): void
    {
        $edisMax = $this->configuredEdisMax(['locale' => 'fr_FR']);

        $this->assertSame('title_txt_fr^10 collection_txt_fr^2 slug_s', $edisMax->getQueryFields());
        $this->assertSame('title_txt_fr^20 collection_txt_fr^2', $edisMax->getPhraseFields());
    }

    /**
     * getFormattedQuery() is deprecated but still consumed by downstream handlers
     * that compose their own query string. Keep its output stable until removal.
     *
     * @group legacy
     */
    public function testDeprecatedFormattedQueryKeepsItsLuceneShape(): void
    {
        $this->assertSame(
            ['"King Lear"~2', '(King~2 AND Lear~2)', 'King\\ Lear*~2'],
            $this->getFormattedQuery('King Lear')
        );
    }

    public function testPublicationDateBoostUsesMultiplicativeBoostFunction(): void
    {
        $handler = $this->createHandler();
        $handler->boostByPublicationDate();

        $query = new Query();
        $args = [];
        $method = new \ReflectionMethod($handler, 'configureQueryParser');
        $method->invokeArgs($handler, [$query, &$args, false]);

        $this->assertSame(
            'recip(ms(NOW,published_at_dt),3.16e-11,1,1)',
            $query->getEDisMax()->getBoostFunctionsMult()
        );
    }
}
