<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\GeneratedEntity\NSArticle;
use App\GeneratedEntity\Repository\NSArticleRepository;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/*
 * This test case requires a running database server and Article fixtures.
 */
class ArticleTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    public function testRepository(): void
    {
        $article = static::getContainer()->get(NSArticleRepository::class)->findOneBy([]);
        $this->assertNotNull($article);
        $this->assertInstanceOf(NSArticle::class, $article);
    }

    public function testCollection(): void
    {
        $articleCount = static::getContainer()->get(NSArticleRepository::class)->countBy([]);

        static::createClient()->request('GET', '/api/articles');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/Article',
            '@id' => '/api/articles',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => $articleCount,
        ]);
        $this->assertResponseHasHeader('Content-Type');
    }

    public function testSingleArticle(): void
    {
        $urlGenerator = static::getContainer()->get(UrlGeneratorInterface::class);
        $article = static::getContainer()->get(NSArticleRepository::class)->findOneBy([]);
        if (null === $article) {
            $this->fail('No article found in database.');
        }

        $this->assertInstanceOf(NSArticle::class, $article);

        static::createClient()->request('GET', '/api/articles/'.$article->getId());

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/Article',
            '@id' => '/api/articles/'.$article->getId(),
            '@type' => 'Article',
            'title' => $article->getTitle(),
            'url' => $urlGenerator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [
                RouteObjectInterface::ROUTE_OBJECT => $article,
            ]),
        ]);
    }

    public function testArticleWebResponse(): void
    {
        $articleRepository = static::getContainer()->get(NSArticleRepository::class);
        $queryBuilder = $articleRepository->createQueryBuilder('a');

        $articleRepository->resetStatuses();
        $articleRepository->alterQueryBuilderWithAuthorizationChecker(
            $queryBuilder,
            'a',
        );

        $article = $queryBuilder
            ->andWhere('n.parent IS NOT NULL')
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $article) {
            $this->fail('No child article found in database.');
        }
        $this->assertInstanceOf(NSArticle::class, $article);

        $client = static::createClient();
        $response = $client->request('GET', '/api/articles/'.$article->getId());
        $this->assertResponseIsSuccessful('/api/articles/'.$article->getId().' endpoint is not accessible.');

        $articleData = $response->toArray(false);
        $path = $articleData['url'] ?? null;
        if (!\is_string($path) || '' === $path) {
            $this->fail('Article URL is missing from API response.');
        }

        $normalizedPath = parse_url($path, \PHP_URL_PATH);
        if (\is_string($normalizedPath) && '' !== $normalizedPath) {
            $path = $normalizedPath;
        }

        $client->request('GET', '/api/web_response_by_path', [
            'query' => [
                'path' => $path,
            ],
        ]);

        $this->assertResponseIsSuccessful('/api/web_response_by_path endpoint is not accessible with path: '.$path.' for article ID: '.$article->getId());
        $this->assertJsonContains([
            '@context' => '/api/contexts/WebResponse',
            '@id' => '/api/web_response_by_path',
            '@type' => 'WebResponse',
        ]);
    }
}
